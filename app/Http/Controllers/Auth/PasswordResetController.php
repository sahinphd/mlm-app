<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([ 'email' => 'required|string' ]);

        $input = $request->email;
        $loginField = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $users = \App\Models\User::where($loginField, $input)->get();

        if ($users->isEmpty()) {
            return back()->withErrors(['email' => 'No account found with this identifier.']);
        }

        if ($users->count() === 1) {
            return $this->processSingleReset($users[0]);
        }

        // Handle duplicates
        $request->session()->put('reset_accounts', array_map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $this->maskEmail($u->email)
            ];
        }, $users->all()));

        return redirect()->route('password.select_account');
    }

    public function showSelectAccount(Request $request)
    {
        $accounts = $request->session()->get('reset_accounts');
        if (!$accounts) return redirect()->route('password.request');

        return view('auth.select-reset-account', compact('accounts'));
    }

    public function selectAccount(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $accounts = $request->session()->get('reset_accounts');
        if (!$accounts) return redirect()->route('password.request');

        $user = \App\Models\User::find($request->user_id);
        $request->session()->forget('reset_accounts');

        return $this->processSingleReset($user);
    }

    protected function processSingleReset($user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
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

    public function showResetForm($token)
    {
        $email = request()->query('email');
        return view('auth.passwords.reset', compact('token', 'email'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->password = bcrypt($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login.view')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
