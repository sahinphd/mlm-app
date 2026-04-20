<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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
            'aadhaar_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nominee_name' => 'nullable|string|max:255',
            'nominee_relation' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->aadhaar_number = $data['aadhaar_number'];
        $user->pan_number = $data['pan_number'];
        $user->address = $data['address'];
        $user->nominee_name = $data['nominee_name'];
        $user->nominee_relation = $data['nominee_relation'];

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }

            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->avatar = 'images/user/' . $filename;
        }

        $user->save();

        return redirect()->back()->with('status', 'Profile updated successfully.');
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
