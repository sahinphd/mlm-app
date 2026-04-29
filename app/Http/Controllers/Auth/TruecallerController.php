<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TruecallerController extends Controller
{
    /**
     * Handle the Truecaller callback.
     * This receives the verified user profile from the frontend.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            // Profile data can include name, email, etc.
            'name' => 'nullable|string',
            'email' => 'nullable|string',
        ]);

        $phone = $request->phone;
        
        // Normalize phone number if needed (e.g., removing +, spaces)
        // For simplicity, we assume the frontend sends it in a format matching our DB
        
        $users = User::where('phone', 'LIKE', '%' . $phone . '%')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this mobile number. Please register first.',
                'redirect' => route('register.view')
            ], 404);
        }

        // If exactly one user found, log them in
        if ($users->count() === 1) {
            Auth::login($users[0], true);
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')
            ]);
        }

        // If multiple users share the phone number, use the selection flow
        $accounts = $users->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $this->maskEmail($u->email)
            ];
        })->toArray();

        $request->session()->put('login_accounts', $accounts);
        $request->session()->put('login_remember', true);

        return response()->json([
            'success' => true,
            'redirect' => route('login.select_account')
        ]);
    }

    private function maskEmail($email)
    {
        if (!$email) return 'N/A';
        $parts = explode('@', $email);
        $name = $parts[0];
        $len = strlen($name);
        if ($len <= 2) return $email;
        return substr($name, 0, 1) . str_repeat('*', $len - 2) . substr($name, -1) . '@' . $parts[1];
    }
}
