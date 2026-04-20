<?php

namespace App\Http\Controllers;

use App\Models\EmiSchedule;
use App\Models\Penalty;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $emis = EmiSchedule::where('user_id', $user->id)
            ->orderBy('due_date', 'asc')
            ->paginate(15, ['*'], 'emis');
            
        $penalties = Penalty::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'penalties');

        return view('credit.emis', [
            'emis' => $emis,
            'penalties' => $penalties,
            'page' => 'emi_schedule'
        ]);
    }

    public function payEmi(Request $request, $id)
    {
        $user = Auth::user();
        $emi = EmiSchedule::where('user_id', $user->id)->findOrFail($id);

        if ($emi->status === 'paid') {
            return back()->with('error', 'This EMI is already paid.');
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->main_balance < $emi->installment_amount) {
            return back()->with('error', 'Insufficient main wallet balance.');
        }

        return DB::transaction(function () use ($user, $emi, $wallet) {
            // Deduct from wallet
            $wallet->main_balance -= $emi->installment_amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'emi',
                'amount' => $emi->installment_amount,
                'reference_id' => 'emi:' . $emi->id,
                'description' => 'EMI Payment for Order #' . $emi->order_id
            ]);

            // Update credit account
            $ca = CreditAccount::where('user_id', $user->id)->first();
            if ($ca) {
                $ca->used_credit = max(0, $ca->used_credit - $emi->installment_amount);
                $ca->available_credit = min($ca->credit_limit, $ca->available_credit + $emi->installment_amount);
                $ca->save();

                CreditTransaction::create([
                    'credit_account_id' => $ca->id,
                    'type' => 'credit',
                    'amount' => $emi->installment_amount,
                    'source' => 'repayment',
                    'reference_id' => 'emi:' . $emi->id,
                    'description' => 'EMI Repayment'
                ]);
            }

            // Update EMI status
            $emi->status = 'paid';
            $emi->save();

            return back()->with('success', 'EMI paid successfully.');
        });
    }

    public function payPenalty(Request $request, $id)
    {
        $user = Auth::user();
        $penalty = Penalty::where('user_id', $user->id)->findOrFail($id);

        if ($penalty->status === 'paid') {
            return back()->with('error', 'This penalty is already paid.');
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->main_balance < $penalty->amount) {
            return back()->with('error', 'Insufficient main wallet balance.');
        }

        return DB::transaction(function () use ($user, $penalty, $wallet) {
            // Deduct from wallet
            $wallet->main_balance -= $penalty->amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'source' => 'penalty',
                'amount' => $penalty->amount,
                'reference_id' => 'penalty:' . $penalty->id,
                'description' => 'Penalty Payment'
            ]);

            // Update penalty status
            $penalty->status = 'paid';
            $penalty->save();

            return back()->with('success', 'Penalty paid successfully.');
        });
    }
}
