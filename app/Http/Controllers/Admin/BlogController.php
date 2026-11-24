<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = Blog::query()->with('author');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%")
                  ->orWhere('meta_keywords', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $blogs = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Blog::select('category')->distinct()->pluck('category');

        return view('admin.blogs.index', compact('blogs', 'categories'));
    }

    public function create()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('admin.blogs.create');
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:published,draft',
            'published_at' => 'nullable|date',
            'image_file' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:255',
        ]);

        // Process Slug
        if (empty($data['slug'])) {
            $data['slug'] = Blog::generateUniqueSlug($data['title']);
        } else {
            $data['slug'] = Blog::generateUniqueSlug($data['slug']);
        }

        // Process Image
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $ext;
            $destination = public_path('images/blogs');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/blogs/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        } else {
            $data['image'] = null;
        }

        unset($data['image_file'], $data['image_url']);

        $data['author_id'] = Auth::id();
        $data['author_name'] = Auth::user()->name;
        $data['category'] = !empty($data['category']) ? trim($data['category']) : 'General';

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blog = Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article created successfully!');
    }

    public function edit(Blog $blog)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:published,draft',
            'published_at' => 'nullable|date',
            'image_file' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string|max:1000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|url|max:255',
        ]);

        // Process Slug
        if (empty($data['slug'])) {
            $data['slug'] = Blog::generateUniqueSlug($data['title'], $blog->id);
        } elseif ($data['slug'] !== $blog->slug) {
            $data['slug'] = Blog::generateUniqueSlug($data['slug'], $blog->id);
        }

        // Process Image
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $ext;
            $destination = public_path('images/blogs');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/blogs/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }

        unset($data['image_file'], $data['image_url']);

        $data['category'] = !empty($data['category']) ? trim($data['category']) : 'General';

        if ($data['status'] === 'published' && empty($blog->published_at) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article updated successfully!');
    }

    public function destroy(Blog $blog)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article deleted successfully.');
    }
}
