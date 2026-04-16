<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
            'main_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->limit(50)->get();

        return response()->json(['wallet' => $wallet, 'transactions' => $transactions]);
    }

    public function credit(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['amount' => 'required|numeric|min:0.01','source' => 'nullable|string']);
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance'=>0,'earning_balance'=>0,'credit_balance'=>0]);

        $wallet->main_balance += $data['amount'];
        $wallet->save();

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'source' => $data['source'] ?? 'manual',
            'amount' => $data['amount'],
            'reference_id' => $request->input('reference_id'),
            'description' => $request->input('description'),
        ]);

        return response()->json(['message' => 'Wallet credited', 'wallet' => $wallet]);
    }

    public function debit(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate(['amount' => 'required|numeric|min:0.01','source'=>'nullable|string']);
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['main_balance'=>0,'earning_balance'=>0,'credit_balance'=>0]);

        if ($wallet->main_balance < $data['amount']) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $wallet->main_balance -= $data['amount'];
        $wallet->save();

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'source' => $data['source'] ?? 'purchase',
            'amount' => $data['amount'],
            'reference_id' => $request->input('reference_id'),
            'description' => $request->input('description'),
        ]);

        return response()->json(['message' => 'Wallet debited', 'wallet' => $wallet]);
    }
}
