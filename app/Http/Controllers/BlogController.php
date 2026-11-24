<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Public Blog Listing page. Accessible without login.
     */
    public function index(Request $request)
    {
        $query = Blog::published()->with('author');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                  ->orWhere('excerpt', 'like', "%{$q}%")
                  ->orWhere('content', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('meta_keywords', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $featured = null;
        if (!$request->filled('q') && !$request->filled('category') && !$request->filled('page')) {
            $featured = (clone $query)->orderBy('views', 'desc')->first();
        }

        $blogs = $query->orderBy('published_at', 'desc')->paginate(9)->withQueryString();
        $categories = Blog::published()->select('category')
            ->selectRaw('count(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        $recentPosts = Blog::published()->orderBy('published_at', 'desc')->take(5)->get();

        return view('blog.index', compact('blogs', 'featured', 'categories', 'recentPosts'));
    }

    /**
     * Public Single Blog Post page. Accessible without login with full SEO meta.
     */
    public function show($slug)
    {
        $blog = Blog::published()->where('slug', $slug)->firstOrFail();

        // Increment view count atomically
        $blog->increment('views');

        $relatedPosts = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->take(3)
            ->get();

        if ($relatedPosts->count() < 3) {
            $needed = 3 - $relatedPosts->count();
            $morePosts = Blog::published()
                ->where('id', '!=', $blog->id)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->orderBy('published_at', 'desc')
                ->take($needed)
                ->get();
            $relatedPosts = $relatedPosts->concat($morePosts);
        }

        return view('blog.show', compact('blog', 'relatedPosts'));
    }
}
