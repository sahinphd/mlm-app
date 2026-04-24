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
            'email' => 'required_without:user_id|nullable|email',
            'user_id' => 'required_without:email|nullable|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        $sender = Auth::user();
        if ($request->user_id) {
            $recipient = User::find($request->user_id);
        } else {
            $recipient = User::where('email', $request->email)->first();
        }

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
                'description' => "Transferred to {$recipient->name} ({$recipient->email}). Remarks: " . ($request->remarks ?? 'N/A'),
            ]);

            // Record transaction for recipient
            WalletTransaction::create([
                'wallet_id' => $recipientWallet->id,
                'type' => 'credit',
                'source' => 'transfer',
                'amount' => $request->amount,
                'description' => "Received from {$sender->name} ({$sender->email}). Remarks: " . ($request->remarks ?? 'N/A'),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Balance transferred successfully to ' . $recipient->name,
                    'new_balance' => number_format($senderWallet->main_balance, 2)
                ]);
            }

            return redirect()->route('wallet.transfer')->with('success', 'Balance transferred successfully to ' . $recipient->name);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'An error occurred during transfer: ' . $e->getMessage()])->withInput();
        }
    }

    public function search(Request $request)
    {
        $term = $request->query('q');
        if (empty($term)) return response()->json([]);

        $users = User::where('status', 'active')
            ->where('id', '!=', Auth::id())
            ->where(function($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%")
                    ->orWhere('id', 'LIKE', "%{$term}%");
            })
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json($users);
    }
}
