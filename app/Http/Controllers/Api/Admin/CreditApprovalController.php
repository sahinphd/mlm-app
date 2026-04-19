<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CreditApprovalController extends Controller
{
    protected function ensureAdmin()
    {
        $user = Auth::user();
        if (! $user || ! method_exists($user, 'isAdmin') || ! $user->isAdmin()) {
            abort(403, 'Forbidden');
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        $list = CreditAccount::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $list]);
    }

    public function approve(Request $request, $id)
    {
        $this->ensureAdmin();
        $ca = CreditAccount::findOrFail($id);
        $data = $request->validate(['credit_limit' => 'required|numeric|min:0']);
        $ca->credit_limit = $data['credit_limit'];
        $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
        $ca->approval_status = 'approved';
        $ca->save();

        \App\Models\CreditTransaction::create([
            'credit_account_id' => $ca->id,
            'type' => 'credit',
            'amount' => $ca->credit_limit,
            'source' => 'manual',
            'description' => 'Credit limit approved/updated by admin'
        ]);
        
        return response()->json(['message' => 'Approved', 'credit_account' => $ca]);
    }

    public function reject(Request $request, $id)
    {
        $this->ensureAdmin();
        $ca = CreditAccount::findOrFail($id);
        $ca->approval_status = 'rejected';
        $ca->save();
        return response()->json(['message' => 'Rejected', 'credit_account' => $ca]);
    }
}
