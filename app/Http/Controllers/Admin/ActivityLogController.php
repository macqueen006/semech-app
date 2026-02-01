<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $selectedUsers = $request->input('users', []);
        $selectedTypes = $request->input('types', []);
        $selectedSubjects = $request->input('subjects', []);
        $limit = $request->input('limit', 20);

        // Build query
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhere('log_name', 'like', '%' . $search . '%');
            });
        }

        if (!empty($selectedUsers)) {
            $query->whereIn('causer_id', $selectedUsers)
                ->where('causer_type', 'App\\Models\\User');
        }

        if (!empty($selectedTypes)) {
            $query->whereIn('description', $selectedTypes);
        }

        if (!empty($selectedSubjects)) {
            $query->whereIn('subject_type', $selectedSubjects);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $activities = $limit == 0 ? $query->get() : $query->paginate($limit);

        // Get filter options
        $users = User::select('id', 'firstname', 'lastname', 'email')
            ->whereIn('id', Activity::distinct()->pluck('causer_id'))
            ->get();

        $eventTypes = Activity::distinct()
            ->pluck('description')
            ->filter()
            ->sort()
            ->values();

        $subjectTypes = Activity::distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(function ($type) {
                return class_basename($type);
            })
            ->sort()
            ->values();

        return view('admin.activity-log.index', compact(
            'activities',
            'users',
            'eventTypes',
            'subjectTypes',
            'search',
            'dateFrom',
            'dateTo',
            'selectedUsers',
            'selectedTypes',
            'selectedSubjects',
            'limit'
        ));
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->can('activity-log-delete')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete activity logs.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer'
            ]);

            // Check if any records actually exist
            $existingIds = Activity::whereIn('id', $validated['ids'])->pluck('id')->toArray();

            if (empty($existingIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid activities found to delete.'
                ], 404);
            }

            $count = Activity::whereIn('id', $existingIds)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} activity log(s) deleted successfully."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Activity log bulk delete failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting activity logs.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $activity = Activity::with(['causer', 'subject'])->findOrFail($id);

            return response()->json([
                'id' => $activity->id,
                'description' => $activity->description,
                'log_name' => $activity->log_name,
                'subject_type' => $activity->subject_type,
                'subject_type_basename' => class_basename($activity->subject_type),
                'subject_id' => $activity->subject_id,
                'created_at_formatted' => $activity->created_at->format('M d, Y H:i:s'),
                'causer' => $activity->causer ? [
                    'firstname' => $activity->causer->firstname,
                    'lastname' => $activity->causer->lastname,
                    'email' => $activity->causer->email,
                    'image_path' => $activity->causer->image_path ? asset($activity->causer->image_path) : null,
                ] : null,
                'properties' => $activity->properties,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Activity log show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the activity log.'
            ], 500);
        }
    }
}
