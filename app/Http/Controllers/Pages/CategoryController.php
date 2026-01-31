<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $perPage = 6;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $posts = Post::with([
            'category' => fn($q) => $q->select('id', 'name', 'backgroundColor', 'textColor'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'image_path'),
        ])
            ->where('category_id', $category->id)
            ->isLive()
            ->notExpired()
            ->offset($offset)
            ->limit($perPage)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPosts = Post::where('category_id', $category->id)
            ->isLive()
            ->notExpired()
            ->count();

        $hasMore = $totalPosts > ($offset + $perPage);
        $hasPrevious = $page > 1;

        return view('pages.category', compact('category', 'posts', 'page', 'hasMore', 'hasPrevious', 'slug'));
    }
}
