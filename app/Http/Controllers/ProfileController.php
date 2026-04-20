<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', [
            'user' => $user,
            'page' => 'profile'
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->save();

        return redirect('/payments')->with('status', 'Profile updated. You can now request a wallet top-up.');
    }

    public function idCard()
    {
        $user = Auth::user();
        $user->load('referralRecord');
        return view('profile.id_card', [
            'user' => $user,
            'page' => 'profile_id'
        ]);
    }
}
