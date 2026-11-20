<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletTransferController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

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

        try {
            $result = $this->ledgerService->recordQrTransfer($sender, $recipient, (float) $request->amount, $request->remarks);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Balance transferred successfully to ' . $recipient->name,
                    'transaction_id' => $result['transaction_id'],
                    'new_balance' => number_format($result['sender_new_balance'], 2)
                ]);
            }

            return redirect()->route('wallet.transfer')->with('success', 'Balance transferred successfully to ' . $recipient->name);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
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
