<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $ca = CreditAccount::firstOrCreate(['user_id' => $user->id], [
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'pending',
        ]);

        return response()->json(['credit_account' => $ca]);
    }

    // User can request credit approval/change - mark pending
    public function requestApproval(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['credit_limit' => 'required|numeric|min:0']);
        $ca = CreditAccount::firstOrCreate(['user_id' => $user->id]);
        $ca->credit_limit = $data['credit_limit'];
        $ca->available_credit = max(0, $data['credit_limit'] - $ca->used_credit);
        $ca->approval_status = 'pending';
        $ca->save();

        return response()->json(['message' => 'Credit request submitted', 'credit_account' => $ca]);
    }
}
