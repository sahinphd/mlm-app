<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $products = Product::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bv' => 'required|numeric|min:0',
            'image' => 'nullable',
            'image_file' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
            $destination = public_path('images/products');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/products/' . $filename;
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
            $destination = public_path('images/products');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/products/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        } else {
            $data['image'] = null;
        }

        unset($data['image_file'], $data['image_url']);

        Product::create($data);

        return redirect()->route('admin.products')->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'bv' => 'required|numeric|min:0',
            'image' => 'nullable',
            'image_file' => 'nullable|image|max:5120',
            'image_url' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'product_' . $product->id . '_' . time() . '.' . $ext;
            $destination = public_path('images/products');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $oldRaw = $product->getRawOriginal('image');
            if (!empty($oldRaw) && !str_starts_with($oldRaw, 'http') && file_exists(public_path($oldRaw))) {
                @unlink(public_path($oldRaw));
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/products/' . $filename;
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'product_' . $product->id . '_' . time() . '.' . $ext;
            $destination = public_path('images/products');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $oldRaw = $product->getRawOriginal('image');
            if (!empty($oldRaw) && !str_starts_with($oldRaw, 'http') && file_exists(public_path($oldRaw))) {
                @unlink(public_path($oldRaw));
            }
            $file->move($destination, $filename);
            $data['image'] = 'images/products/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        } elseif ($request->boolean('remove_image')) {
            $oldRaw = $product->getRawOriginal('image');
            if (!empty($oldRaw) && !str_starts_with($oldRaw, 'http') && file_exists(public_path($oldRaw))) {
                @unlink(public_path($oldRaw));
            }
            $data['image'] = null;
        } else {
            // Keep existing image intact
            unset($data['image']);
        }

        unset($data['image_file'], $data['image_url'], $data['remove_image']);

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $oldRaw = $product->getRawOriginal('image');
        if (!empty($oldRaw) && !str_starts_with($oldRaw, 'http') && file_exists(public_path($oldRaw))) {
            @unlink(public_path($oldRaw));
        }
        $product->delete();
        return response()->json(['success' => true]);
    }
}
