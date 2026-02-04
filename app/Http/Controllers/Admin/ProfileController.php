<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProfileUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ImageStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        return view('admin.profile.index', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (AJAX).
     */
    public function update(AdminProfileUpdateRequest $request)
    {
        $user = $request->user();
        $oldImagePath = $user->image_path;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle avatar update
        if ($request->has('image_path')) {
            $user->image_path = $request->input('image_path');

            // Delete old avatar if it's different and not default
            if ($oldImagePath &&
                $oldImagePath !== $request->input('image_path') &&
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

        $user->save();

        // Check if request expects JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'image_path' => $user->image_path,
                    'bio' => $user->bio,
                    'website' => $user->website,
                    'twitter' => $user->twitter,
                    'linkedin' => $user->linkedin,
                    'github' => $user->github,
                ]
            ]);
        }

        return Redirect::route('admin.profile')->with('status', 'profile-updated');
    }

    /**
     * Upload avatar (AJAX)
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        try {
            $imageService = app(ImageStorageService::class);
            $uploadedPath = $imageService->storeAvatar($request->file('image'));

            \Log::info('Profile avatar uploaded successfully', ['path' => $uploadedPath]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully!',
                'path' => $uploadedPath
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading profile avatar: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete avatar (AJAX)
     */
    public function deleteAvatar(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $imageService = app(ImageStorageService::class);
            $imageService->deleteByPath($request->path);

            return response()->json([
                'success' => true,
                'message' => 'Avatar removed successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting profile avatar: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar. Please try again.'
            ], 500);
        }
    }

    /**
     * Cleanup orphaned avatars
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

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
