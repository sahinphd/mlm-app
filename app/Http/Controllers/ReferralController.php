<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $referredUsers = $user->referredUsers()->orderBy('created_at', 'desc')->paginate(20);
        $referralRecord = $user->referralRecord;

        if (!$referralRecord) {
            // Auto-generate if missing
            $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            while (\App\Models\Referral::where('referral_code', $newCode)->exists()) {
                $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            }
            $referralRecord = \App\Models\Referral::create([
                'user_id' => $user->id,
                'parent_id' => null,
                'referral_code' => $newCode,
                'level_depth' => 0,
            ]);
        }
        
        $page = 'referrals';
        return view('referrals.index', compact('referredUsers', 'referralRecord', 'page'));
    }

    public function genealogy(Request $request)
    {
        $user = Auth::user();
        $rootId = $request->query('root_id', $user->id);
        
        // Security: Ensure the requested root is actually in the authenticated user's downline
        $rootUser = \App\Models\User::find($rootId);
        if (!$rootUser) {
            return redirect()->route('genealogy.index');
        }

        if ($rootId != $user->id) {
            $isDownline = false;
            $current = $rootUser;
            // Traverse up to check if Auth::user() is an ancestor
            while ($current && $current->referralRecord && $current->referralRecord->parent_id) {
                if ($current->referralRecord->parent_id == $user->id) {
                    $isDownline = true;
                    break;
                }
                $current = \App\Models\User::find($current->referralRecord->parent_id);
            }
            if (!$isDownline) {
                $rootUser = $user; // Reset to self if not authorized
            }
        }

        // Fetch 3 levels starting from the determined root
        $genealogy = $this->getReferralTree($rootUser, 1, 3);
        $page = 'genealogy';
        
        return view('genealogy.index', compact('genealogy', 'rootUser', 'page'));
    }

    private function getReferralTree($user, $currentLevel, $maxLevel)
    {
        if ($currentLevel > $maxLevel) {
            return [];
        }

        $referrals = \App\Models\User::whereHas('referralRecord', function($query) use ($user) {
            $query->where('parent_id', $user->id);
        })->get();

        $tree = [];
        foreach ($referrals as $referral) {
            $tree[] = [
                'user' => $referral,
                'level' => $currentLevel,
                'children' => $this->getReferralTree($referral, $currentLevel + 1, $maxLevel)
            ];
        }

        return $tree;
    }
}
