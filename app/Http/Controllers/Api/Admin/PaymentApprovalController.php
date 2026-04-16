<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PaymentApprovalController extends Controller
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
        $requests = PaymentRequest::orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $requests]);
    }

    public function approve(Request $request, $id)
    {
        $this->ensureAdmin();
        $pr = PaymentRequest::findOrFail($id);
        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Already processed'], 400);
        }

        // Update payment request
        $pr->status = 'approved';
        $pr->admin_note = $request->input('admin_note');
        $pr->processed_at = Carbon::now();
        $pr->save();

        // Ensure wallet exists
        $wallet = $pr->user->wallet;
        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $pr->user_id,
                'main_balance' => 0,
                'earning_balance' => 0,
                'credit_balance' => 0,
            ]);
        }

        // Credit user's main balance
        $wallet->main_balance = $wallet->main_balance + $pr->amount;
        $wallet->save();

        // Log transaction
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'source' => 'manual',
            'amount' => $pr->amount,
            'reference_id' => 'payment_request:'.$pr->id,
            'description' => 'Admin approved manual payment request',
        ]);

        return response()->json(['message' => 'Approved', 'data' => $pr]);
    }

    public function reject(Request $request, $id)
    {
        $this->ensureAdmin();
        $pr = PaymentRequest::findOrFail($id);
        if ($pr->status !== 'pending') {
            return response()->json(['message' => 'Already processed'], 400);
        }

        $pr->status = 'rejected';
        $pr->admin_note = $request->input('admin_note');
        $pr->processed_at = Carbon::now();
        $pr->save();

        return response()->json(['message' => 'Rejected', 'data' => $pr]);
    }
}
