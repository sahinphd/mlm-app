<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $referredUsers = $user->referredUsers()->orderBy('created_at', 'desc')->paginate(20);
        $referralRecord = $user->referralRecord;

        if (!$referralRecord) {
            // Auto-generate if missing
            $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            while (\App\Models\Referral::where('referral_code', $newCode)->exists()) {
                $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            }
            $referralRecord = \App\Models\Referral::create([
                'user_id' => $user->id,
                'parent_id' => null,
                'referral_code' => $newCode,
                'level_depth' => 0,
            ]);
        }
        
        $page = 'referrals';
        return view('referrals.index', compact('referredUsers', 'referralRecord', 'page'));
    }
}
