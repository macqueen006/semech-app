<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('title')->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_published' => 'boolean',
        ]);

        $page->update($validated);

        activity('pages')
            ->performedOn($page)
            ->causedBy(auth()->user())
            ->withProperties([
                'page_title' => $page->title,
                'page_slug' => $page->slug,
            ])
            ->log('updated');

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully!',
            'redirect' => route('admin.pages.index')
        ]);
    }

    /**
     * Upload an image for the Quill editor
     */
    public function uploadEditorImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:1024',
        ], [
            'image.required' => 'Please select an image to upload.',
            'image.image' => 'The file must be an image (jpg, jpeg, png, gif, svg, or webp).',
            'image.max' => 'The image size cannot exceed 1MB.',
        ]);

        $imageStorageService = app(\App\Services\ImageStorageService::class);
        $uploadedPath = $imageStorageService->storeImage($request->file('image'), 'pages');

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded and inserted successfully!',
            'path' => $uploadedPath
        ]);
    }

    /**
     * Browse existing images for the editor
     */
    public function browseEditorImages()
    {
        try {
            $imageAnalysisService = app(\App\Services\ImageAnalysisService::class);
            $directories = ['pages'];

            $analysisData = $imageAnalysisService->analyzeDirectories($directories);

            $images = collect($analysisData['files'])
                ->take(100)
                ->toArray();

            return response()->json([
                'success' => true,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load images',
                'images' => []
            ]);
        }
    }

    /**
     * Cleanup orphaned images
     */
    public function cleanupImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'string'
        ], [
            'images.required' => 'No images selected for cleanup.',
            'images.array' => 'Invalid image data format.',
        ]);

        $imageStorageService = app(\App\Services\ImageStorageService::class);
        $deletedCount = 0;

        foreach ($request->images as $imagePath) {
            try {
                $deleted = $imageStorageService->safeDelete($imagePath);
                if ($deleted) {
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to delete orphan image: ' . $imagePath, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} orphaned images",
            'deleted_count' => $deletedCount
        ]);
    }
}
