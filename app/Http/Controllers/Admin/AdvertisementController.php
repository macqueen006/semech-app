<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of advertisements
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $position = $request->input('position', '');
        $status = $request->input('status', '');

        // Build query
        $query = Advertisement::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($position) {
            $query->where('position', $position);
        }

        if ($status !== '') {
            $query->where('is_active', $status);
        }

        $advertisements = $query->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.advertisements.partials.table', compact('advertisements'))->render(),
                'pagination' => $advertisements->links()->render()
            ]);
        }

        return view('admin.advertisements.index', compact('advertisements', 'search', 'position', 'status'));
    }

    /**
     * Show the form for creating a new advertisement
     */
    public function create()
    {
        return view('admin.advertisements.create');
    }

    /**
     * Store a newly created advertisement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|string',
            'link_url' => 'nullable|url',
            'position' => 'required|in:header,sidebar,footer,between-posts,popup',
            'size' => 'required|in:small,medium,large,banner',
            'is_active' => 'boolean',
            'open_new_tab' => 'boolean',
            'display_order' => 'integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');

        Advertisement::create($validated);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement created successfully.');
    }

    /**
     * Show the form for editing the specified advertisement
     */
    public function edit($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    /**
     * Update the specified advertisement
     */
    public function update(Request $request, $id)
    {
        $advertisement = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|string',
            'link_url' => 'nullable|url',
            'position' => 'required|in:header,sidebar,footer,between-posts,popup',
            'size' => 'required|in:small,medium,large,banner',
            'is_active' => 'boolean',
            'open_new_tab' => 'boolean',
            'display_order' => 'integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');

        $advertisement->update($validated);

        return redirect()->route('admin.advertisements.index')
            ->with('success', 'Advertisement updated successfully.');
    }
    /**
     * Remove the specified advertisement
     */
    public function destroy(Request $request, $id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);

            // Delete image file if exists using ImageStorageService
            if ($advertisement->image_path) {
                $imageStorageService = app(\App\Services\ImageStorageService::class);
                $imageStorageService->safeDelete($advertisement->image_path);
            }

            $advertisement->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Advertisement deleted successfully.'
                ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting advertisement: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete advertisement. Please try again.'
                ], 500);


        }
    }

    /**
     * Toggle advertisement status
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $advertisement = Advertisement::findOrFail($id);
            $advertisement->update(['is_active' => !$advertisement->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Advertisement status updated successfully.',
                'is_active' => $advertisement->is_active
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling advertisement status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload advertisement image
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        try {
            $imageStorageService = app(\App\Services\ImageStorageService::class);
            $uploadedPath = $imageStorageService->storeAdvertisement($request->file('image'));

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully!',
                'path' => $uploadedPath
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading advertisement image: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete advertisement image
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $imageStorageService = app(\App\Services\ImageStorageService::class);
            $imageStorageService->safeDelete($request->path);

            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting advertisement image: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image. Please try again.'
            ], 500);
        }
    }
}
