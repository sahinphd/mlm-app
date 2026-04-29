<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TruecallerController extends Controller
{
    /**
     * Handle the Truecaller callback from their server.
     * Receives accessToken and requestNonce.
     */
    public function login(Request $request)
    {
        // Load settings to check if enabled
        $settingsFile = 'settings.json';
        $settings = [];
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
            $settings = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
        }

        if (($settings['truecaller_login'] ?? 'off') !== 'on') {
            return response()->json(['success' => false, 'message' => 'Truecaller login is currently disabled.'], 403);
        }

        Log::info('Truecaller Callback Received', $request->all());

        if ($request->has('accessToken')) {
            return $this->handleOAuthCallback($request);
        }

        // Fallback for direct profile submission (if still used by some flows)
        return $this->handleLegacyCallback($request);
    }

    /**
     * Handle the new OAuth-based flow callback.
     */
    protected function handleOAuthCallback(Request $request)
    {
        $accessToken = $request->accessToken;

        // 1. Fetch user profile from Truecaller API using the accessToken
        $response = Http::withToken($accessToken)
            ->get('https://profile-sdk-noneu.truecaller.com/v1/fetchProfile');

        if ($response->failed()) {
            Log::error('Truecaller Profile Fetch Failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to verify Truecaller profile.'], 401);
        }

        $profile = $response->json();
        Log::info('Truecaller Profile Fetched', $profile);

        $phone = $profile['phoneNumber'] ?? null;
        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Phone number not provided by Truecaller.'], 400);
        }

        // Normalize phone (Truecaller usually sends with +country code)
        // We strip '+' for searching
        $searchPhone = ltrim($phone, '+');

        $users = User::where('phone', 'LIKE', '%' . $searchPhone . '%')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false, 
                'message' => 'No account found with number ' . $phone . '. Please register first.',
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

    /**
     * Original logic for direct profile submission from frontend.
     */
    protected function handleLegacyCallback(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = ltrim($request->phone, '+');
        $users = User::where('phone', 'LIKE', '%' . $phone . '%')->get();

        if ($users->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No account found.', 'redirect' => route('register.view')], 404);
        }

        if ($users->count() === 1) {
            Auth::login($users[0], true);
            return response()->json(['success' => true, 'redirect' => route('dashboard')]);
        }

        $accounts = $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $this->maskEmail($u->email)])->toArray();
        $request->session()->put('login_accounts', $accounts);
        $request->session()->put('login_remember', true);

        return response()->json(['success' => true, 'redirect' => route('login.select_account')]);
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
