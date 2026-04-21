<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletTransferController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
            'main_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        return view('wallet.transfer', [
            'page' => 'wallet_transfer',
            'wallet' => $wallet
        ]);
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        $sender = Auth::user();
        $recipient = User::where('email', $request->email)->first();

        if ($recipient->id === $sender->id) {
            return back()->withErrors(['email' => 'You cannot transfer balance to yourself.'])->withInput();
        }

        $senderWallet = Wallet::firstOrCreate(['user_id' => $sender->id]);
        $recipientWallet = Wallet::firstOrCreate(['user_id' => $recipient->id]);

        if ($senderWallet->main_balance < $request->amount) {
            return back()->withErrors(['amount' => 'Insufficient main balance.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Deduct from sender
            $senderWallet->decrement('main_balance', $request->amount);

            // Add to recipient
            $recipientWallet->increment('main_balance', $request->amount);

            // Record transaction for sender
            WalletTransaction::create([
                'wallet_id' => $senderWallet->id,
                'type' => 'debit',
                'source' => 'transfer',
                'amount' => $request->amount,
                'description' => "Transferred to {$recipient->email}. Remarks: " . ($request->remarks ?? 'N/A'),
            ]);

            // Record transaction for recipient
            WalletTransaction::create([
                'wallet_id' => $recipientWallet->id,
                'type' => 'credit',
                'source' => 'transfer',
                'amount' => $request->amount,
                'description' => "Received from {$sender->email}. Remarks: " . ($request->remarks ?? 'N/A'),
            ]);

            DB::commit();

            return redirect()->route('wallet.history')->with('success', 'Balance transferred successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred during transfer: ' . $e->getMessage()])->withInput();
        }
    }
}
