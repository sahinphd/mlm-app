<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Impersonate a user (Admin logs in as the selected user).
     */
    public function impersonate(Request $request, User $user)
    {
        $admin = Auth::user();

        // 1. Ensure the authenticated caller has admin privileges
        if (!$admin || !$admin->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // 2. Prevent self-impersonation
        if ($admin->id === $user->id) {
            return back()->with('error', 'You are already logged into your own account.');
        }

        // 3. Prevent impersonating another administrator
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot impersonate another administrator account.');
        }

        // 4. Save the original admin credentials in the session
        $request->session()->put('admin_impersonator_id', $admin->id);
        $request->session()->put('admin_impersonator_name', $admin->name);

        // 5. Authenticate as the target user
        Auth::login($user);

        // 6. Determine destination (defaults to profile edit page)
        $target = $request->query('redirect', $request->input('redirect', 'profile'));
        $redirectUrl = ($target === 'dashboard') ? route('dashboard') : route('profile.edit');

        return redirect($redirectUrl)->with('success', "Logged in as {$user->name}. You are in User Impersonation mode.");
    }

    /**
     * Stop impersonation and restore original admin session.
     */
    public function leave(Request $request)
    {
        $adminId = $request->session()->get('admin_impersonator_id');

        if (!$adminId) {
            return redirect('/login');
        }

        $admin = User::find($adminId);

        if (!$admin || !$admin->isAdmin()) {
            $request->session()->forget(['admin_impersonator_id', 'admin_impersonator_name']);
            Auth::logout();
            return redirect('/login')->withErrors(['email' => 'Original admin account not found or lacks privileges.']);
        }

        $impersonatedName = Auth::check() ? Auth::user()->name : 'user';

        // Clear impersonator session keys
        $request->session()->forget(['admin_impersonator_id', 'admin_impersonator_name']);

        // Restore admin authentication
        Auth::login($admin);

        return redirect()->route('admin.customer-lookup.index', ['search' => $impersonatedName])
            ->with('success', "Exited impersonation of {$impersonatedName}. Returned to admin account ({$admin->name}).");
    }
}
