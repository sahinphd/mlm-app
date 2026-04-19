<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $packages = Package::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $products = Product::where('status', 'active')->get();
        return view('admin.packages.create', compact('products'));
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'bv' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $package = Package::create($data);

        foreach ($request->products as $item) {
            $package->products()->attach($item['id'], ['quantity' => $item['quantity']]);
        }

        return redirect()->route('admin.packages')->with('success', 'Package created successfully');
    }

    public function edit(Package $package)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $products = Product::where('status', 'active')->get();
        $packageProducts = $package->products->pluck('pivot.quantity', 'id')->toArray();
        return view('admin.packages.edit', compact('package', 'products', 'packageProducts'));
    }

    public function update(Request $request, Package $package)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'bv' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $package->update($data);

        $syncData = [];
        foreach ($request->products as $item) {
            $syncData[$item['id']] = ['quantity' => $item['quantity']];
        }
        $package->products()->sync($syncData);

        return redirect()->route('admin.packages')->with('success', 'Package updated successfully');
    }

    public function destroy(Package $package)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        $package->delete();
        return response()->json(['success' => true]);
    }
}
