<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\HighlightPost;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public int $offset = 0;
    public function index()
    {
        $posts = Post::with([
            'category' => fn($q) => $q->select('id', 'name', 'backgroundColor', 'textColor'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'image_path'),
        ])
            ->isLive()
            ->notExpired()
            ->select('posts.*', \DB::raw('(SELECT COUNT(*) FROM highlight_posts WHERE post_id = posts.id) > 0 AS is_highlighted'))
            ->limit(20)
            ->orderBy('id', 'desc')
            ->get();

        $allPosts = Post::with([
            'category' => fn($q) => $q->select('id', 'name', 'backgroundColor', 'textColor'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'image_path'),
        ])
            ->isLive()
            ->notExpired()
            ->select('posts.*', \DB::raw('(SELECT COUNT(*) FROM highlight_posts WHERE post_id = posts.id) > 0 AS is_highlighted'))
            ->offset($this->offset)
            ->limit(5)
            ->orderBy('id', 'desc')
            ->get();


        $highlightedPosts = HighlightPost::whereHas('post', fn($q) => $q->where('is_published', 1))->get();

        $trendingTopics = Post::with('category')
            ->isLive()
            ->notExpired()
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('view_count', 'desc')
            ->limit(7)
            ->get();

        $popularPosts = Post::with('category', 'user')
            ->isLive()
            ->notExpired()
            ->orderBy('view_count', 'desc')
            ->limit(3)
            ->get();

        return view('pages.index', [
            'posts' => $posts,
            'allPosts' => $allPosts,
            'highlightedPosts' => $highlightedPosts,
            'trendingTopics' => $trendingTopics,
            'popularPosts' => $popularPosts
        ]);
    }

    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);

        $posts = Post::with([
            'category' => fn($q) => $q->select('id', 'name', 'backgroundColor', 'textColor'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'image_path'),
        ])
            ->isLive()
            ->notExpired()
            ->select('posts.*', \DB::raw('(SELECT COUNT(*) FROM highlight_posts WHERE post_id = posts.id) > 0 AS is_highlighted'))
            ->offset($offset)
            ->limit(5)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['posts' => $posts]);
    }

    public function show()
    {
        return view('pages.article');
    }
}
