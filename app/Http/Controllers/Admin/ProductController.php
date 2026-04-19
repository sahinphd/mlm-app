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
            'image' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

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
            'image' => 'nullable|string|max:1000',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $product->delete();
        return response()->json(['success' => true]);
    }
}
