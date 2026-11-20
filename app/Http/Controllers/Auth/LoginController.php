<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\PhoneHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = trim($data['email']);
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        // 1. Find all users matching the input (email or phone)
        if ($isEmail) {
            $users = User::where('email', $input)->get();
        } else {
            // Flexible phone matching: handles 10-digit, +91, spaces, 0-prefix, etc.
            $users = User::wherePhone($input)->get();
        }

        if ($users->isEmpty()) {
            return back()->withErrors(['email' => 'No account found with these credentials.'])->withInput();
        }

        // 2. Filter users by password matching
        $matchingUsers = [];
        foreach ($users as $user) {
            if (Hash::check($data['password'], $user->password)) {
                $matchingUsers[] = $user;
            }
        }

        if (empty($matchingUsers)) {
            return back()->withErrors(['email' => 'Invalid password.'])->withInput();
        }

        // 3. If exactly one user matches the password, log them in
        if (count($matchingUsers) === 1) {
            Auth::login($matchingUsers[0], $request->filled('remember'));
            return $this->authenticated($request, $matchingUsers[0]);
        }

        // 4. Multiple users share both the same phone AND password
        // Store IDs in session and redirect to selection page
        $request->session()->put('login_accounts', array_map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $this->maskEmail($u->email)
            ];
        }, $matchingUsers));
        
        $request->session()->put('login_remember', $request->filled('remember'));

        return redirect()->route('login.select_account');
    }

    public function showSelectAccount(Request $request)
    {
        $accounts = $request->session()->get('login_accounts');
        if (!$accounts) return redirect()->route('login');

        return view('auth.select-account', compact('accounts'));
    }

    public function selectAccount(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $accounts = $request->session()->get('login_accounts');
        $remember = $request->session()->get('login_remember');

        if (!$accounts) return redirect()->route('login');

        // Security: ensure the selected ID was actually in the selection list
        $accountIds = array_column($accounts, 'id');
        if (!in_array($request->user_id, $accountIds)) {
            return redirect()->route('login');
        }

        $user = User::find($request->user_id);
        Auth::login($user, $remember);

        $request->session()->forget(['login_accounts', 'login_remember']);

        return $this->authenticated($request, $user);
    }

    protected function authenticated(Request $request, $user)
    {
        $request->session()->regenerate();
        $fallback = $user && method_exists($user, 'isAdmin') && $user->isAdmin()
            ? '/admin'
            : '/dashboard'; // Reverted to dashboard as per common route
        return redirect()->intended($fallback);
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
