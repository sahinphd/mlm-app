<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function verify($code)
    {
        $row = DB::table('referrals')->where('referral_code', $code)->first();
        if (! $row) return response()->json(['valid' => false], 404);
        return response()->json(['valid' => true, 'referral' => $row]);
    }

    public function myReferral()
    {
        $user = Auth::user();
        if (! $user) return response()->json(['error'=>'unauthenticated'],401);
        $ref = DB::table('referrals')->where('user_id', $user->id)->first();
        return response()->json(['data' => $ref]);
    }
}
