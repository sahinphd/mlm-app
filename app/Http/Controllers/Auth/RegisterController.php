<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'country' => 'required|string|max:8',
            'phone' => 'required|string|max:30',
            'referral' => 'nullable|string|max:50',
        ], [
            'email.unique' => 'An account with this email already exists. Please login.',
        ]);

        $phoneCombined = trim(($data['country'] ?? '') . ' ' . ($data['phone'] ?? ''));

        // Check for unique phone number manually since it's combined
        if (User::where('phone', $phoneCombined)->exists()) {
            return back()->withErrors(['phone' => 'An account with this mobile number already exists. Please login.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phoneCombined,
            'password' => Hash::make($data['password']),
        ]);

        // Ensure role is always a regular user for public registrations
        $user->role = 'user';
        $user->save();

        // Handle referral: if a referral code was provided, link the new user
        $code = !empty($data['referral']) ? trim($data['referral']) : null;
        
        $parentId = null;
        $parentLevel = 0;

        if ($code) {
            $parent = DB::table('referrals')->where('referral_code', $code)->first();
            if ($parent) {
                $parentId = $parent->user_id;
                $parentLevel = intval($parent->level_depth);
            }
        }

        // generate a referral code for the new user
        $newCode = Str::upper(Str::random(6));
        // ensure uniqueness (rare collision)
        while (DB::table('referrals')->where('referral_code', $newCode)->exists()) {
            $newCode = Str::upper(Str::random(6));
        }

        DB::table('referrals')->insert([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'referral_code' => $newCode,
            'level_depth' => $parentId ? ($parentLevel + 1) : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
