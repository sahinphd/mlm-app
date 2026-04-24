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

        // Lock profile if KYC is pending or approved
        if (in_array($user->kyc_status, ['pending', 'approved'])) {
            return redirect()->back()->withErrors(['kyc' => 'Your profile is locked as KYC is ' . $user->kyc_status . '.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'aadhaar_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'nominee_name' => 'nullable|string|max:255',
            'nominee_relation' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:1024', // 1MB Max
            'submit_kyc' => 'nullable|boolean',
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

        // Check if user wants to submit for KYC
        if ($request->boolean('submit_kyc')) {
            // Validate all required fields for KYC
            if (empty($user->aadhaar_number) || empty($user->pan_number) || empty($user->address) || empty($user->nominee_name) || empty($user->nominee_relation)) {
                return redirect()->back()->withErrors(['kyc' => 'Please fill all KYC and Nominee details before submitting for approval.'])->withInput();
            }
            $user->kyc_status = 'pending';
        }

        $user->save();

        $message = $user->kyc_status === 'pending' ? 'Profile submitted for KYC approval and is now locked.' : 'Profile draft saved successfully.';

        return redirect()->back()->with('status', $message);
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
