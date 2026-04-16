<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $requests = PaymentRequest::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $requests]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $pr = PaymentRequest::create(array_merge($data, ['user_id' => $user->id]));

        return response()->json(['data' => $pr], 201);
    }

    public function show($id)
    {
        $user = Auth::user();
        $pr = PaymentRequest::where('user_id', $user->id)->findOrFail($id);
        return response()->json(['data' => $pr]);
    }
}
