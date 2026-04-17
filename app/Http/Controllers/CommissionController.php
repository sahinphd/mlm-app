<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $commissions = Commission::where('user_id', $user->id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('commissions.index', compact('commissions'));
    }

    public function adminIndex(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $commissions = Commission::with(['user', 'fromUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
            
        return view('admin.commissions.index', compact('commissions'));
    }
}
