<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $products]);
    }

    public function show($id)
    {
        $p = Product::findOrFail($id);
        return response()->json(['data' => $p]);
    }
}
