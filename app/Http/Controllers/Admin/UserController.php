<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageStorageService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Notifications\RoleNotification;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $order = $request->input('order', 'desc');
        $limit = $request->input('limit', 20);
        $selectedRoles = $request->input('roles', []);

        // Build query
        $query = User::orderBy('id', $order);

        if ($search) {
            $keywords = explode(' ', $search);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('firstname', 'like', '%' . $keyword . '%')
                        ->orWhere('lastname', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
                }
            });
        }

        if (!empty($selectedRoles)) {
            $query->whereHas('roles', function ($q) use ($selectedRoles) {
                $q->whereIn('id', $selectedRoles);
            });
        }

        $users = $limit == 0
            ? $query->with('roles')->get()
            : $query->with('roles')->paginate($limit);

        $roles = Role::withCount('users')->get();

        // Get user statistics
        $userStats = User::withCount(['posts'])
            ->selectRaw('users.*, COALESCE(SUM(posts.view_count), 0) as total_views')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            ->groupBy('users.id')
            ->get()
            ->keyBy('id');

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.users.partials.table', compact('users', 'userStats'))->render(),
                'pagination' => $limit > 0 ? $users->links()->render() : ''
            ]);
        }

        return view('admin.users.index', compact('users', 'roles', 'userStats', 'search', 'order', 'limit', 'selectedRoles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'image_path' => '/images/user.jpg',
        ]);

        $user->assignRole($validated['roles']);

        if ($request->has('send_mail')) {
            // Handle email sending here
            // You can implement this based on your mail service
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id)
    {
        $user = User::with('roles')->findOrFail($id);

        $stats = User::withCount(['posts'])
            ->selectRaw('users.*, COALESCE(SUM(posts.view_count), 0) as total_views')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            ->where('users.id', $id)
            ->groupBy('users.id')
            ->first();

        $userData = [
            'user' => $user,
            'posts_count' => $stats->posts_count ?? 0,
            'total_views' => $stats->total_views ?? 0,
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.users.partials.modal', compact('userData'))->render()
            ]);
        }

        return view('admin.users.show', compact('userData'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // Check if editing own profile
        if ($user->id === auth()->id()) {
            abort(403, 'Cannot edit your own user record here.');
        }

        // Check if trying to edit admin as non-admin
        if (!empty($user->roles[0])) {
            if ($user->roles[0]->name == 'Admin' && !auth()->user()->hasRole('Admin')) {
                abort(403);
            }
        }

        $roles = Role::pluck('name', 'name')->all();

        if (!auth()->user()->hasRole('Admin')) {
            unset($roles['Admin']);
            if (auth()->user()->roles->isNotEmpty()) {
                unset($roles[auth()->user()->roles[0]->name]);
            }
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent editing admin as non-admin
        if (!empty($user->roles[0])) {
            if ($user->roles[0]->name == 'Admin' && !auth()->user()->hasRole('Admin')) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'roles' => 'required|array',
            'image_path' => 'nullable|string',
        ]);

        $oldRole = $user->roles->isNotEmpty() ? $user->roles[0]->name : null;
        $oldImagePath = $user->image_path;

        $input = [
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'email' => $validated['email'],
        ];

        // Handle password
        if (!empty($request->password)) {
            $input['password'] = Hash::make($request->password);
        }

        // Handle avatar update
        if ($request->has('image_path')) {
            $input['image_path'] = $validated['image_path'];

            // Delete old avatar if it's different and not default
            if ($oldImagePath &&
                $oldImagePath !== $validated['image_path'] &&
                !str_contains($oldImagePath, 'user.jpg') &&
                !str_contains($oldImagePath, 'default-avatar')) {

                try {
                    $imageService = app(ImageStorageService::class);
                    $imageService->safeDelete($oldImagePath);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete old avatar: ' . $e->getMessage());
                }
            }
        }

        $user->update($input);

        // Update roles
        if ($validated['roles']) {
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            $user->assignRole($validated['roles']);
        }

        // Check for role change and send notification
        $newRole = $user->fresh()->roles->isNotEmpty() ? $user->fresh()->roles[0]->name : null;

        if ($oldRole != $newRole && auth()->id() !== $user->id) {
            $user->notify(new RoleNotification('INFO', 'A new role has been assigned: ' . $newRole . '.'));
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (auth()->id() == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete yourself.'
                ], 403);
            }

            $user = User::findOrFail($id);

            if ($user->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete admin user.'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete user. Please try again.'
            ], 500);
        }
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        try {
            $userIds = $request->input('user_ids', []);

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users selected'
                ], 400);
            }

            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($userIds as $userId) {
                if ($userId === auth()->id()) {
                    $skippedCount++;
                    continue;
                }

                $user = User::find($userId);

                if (!$user || $user->hasRole('Admin')) {
                    $skippedCount++;
                    continue;
                }

                $user->delete();
                $deletedCount++;
            }

            $message = "Successfully deleted {$deletedCount} user(s)";
            if ($skippedCount > 0) {
                $message .= ". Skipped {$skippedCount} user(s) (admins or yourself)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount,
                'skipped' => $skippedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error bulk deleting users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload user avatar (just upload, don't delete anything)
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'user_id' => 'nullable|exists:users,id'
        ]);

        try {
            $imageService = app(ImageStorageService::class);
            $uploadedPath = $imageService->storeAvatar($request->file('image'));

            \Log::info('Avatar uploaded successfully', ['path' => $uploadedPath]);

            // ✅ JUST UPLOAD - Don't delete anything here
            // Cleanup will happen in update() method or via beforeunload cleanup

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully!',
                'path' => $uploadedPath
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading avatar: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $imageService = app(ImageStorageService::class);
            $imageService->deleteImageByPath($request->path);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting avatar: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Cleanup orphaned avatars (called when user abandons edit page)
     */
    public function cleanupAvatars(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'string'
        ]);

        $imageService = app(ImageStorageService::class);
        $deletedCount = 0;

        foreach ($request->images as $imagePath) {
            try {
                // Use deleteByPath instead of safeDelete to force deletion
                $deleted = $imageService->deleteByPath($imagePath);
                if ($deleted) {
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete orphan avatar: ' . $imagePath, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} orphaned avatars",
            'deleted_count' => $deletedCount
        ]);
    }
}
