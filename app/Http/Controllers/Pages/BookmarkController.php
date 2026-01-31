<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = auth()->user()
            ->bookmarks()
            ->with(['post.category', 'post.user'])
            ->latest()
            ->paginate(12);

        return view('pages.bookmark', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id'
        ]);

        $user = auth()->user();
        $bookmark = $user->bookmarks()
            ->where('post_id', $request->post_id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'success' => true,
                'bookmarked' => false,
                'message' => 'Removed from bookmarks'
            ]);
        } else {
            $user->bookmarks()->create([
                'post_id' => $request->post_id
            ]);
            return response()->json([
                'success' => true,
                'bookmarked' => true,
                'message' => 'Added to bookmarks'
            ]);
        }
    }
}
