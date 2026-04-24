<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletHistoryController extends Controller
{
    public function walletIndex()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
            'main_balance' => 0,
            'earning_balance' => 0,
            'credit_balance' => 0,
        ]);

        // Calculate earnings from different commission types
        // Since Joining and Repurchase are now in main_balance, and BV is in earning_balance
        $joiningEarnings = \App\Models\Commission::where('user_id', $user->id)->where('type', 'joining')->sum('amount');
        $repurchaseEarnings = \App\Models\Commission::where('user_id', $user->id)->where('type', 'repurchase')->sum('amount');

        return view('wallet.history', [
            'page' => 'wallet_history',
            'wallet' => $wallet,
            'joiningEarnings' => $joiningEarnings,
            'repurchaseEarnings' => $repurchaseEarnings,
        ]);
    }

    public function walletData(Request $request)
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);
        
        $query = WalletTransaction::where('wallet_id', $wallet->id);

        // Total count
        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('source', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Sorting
        $columns = ['created_at', 'type', 'source', 'amount', 'description'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $transactions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($transactions as $tx) {
            $data[] = [
                'date' => $tx->created_at->format('M d, Y H:i'),
                'type' => $tx->type === 'credit' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Credit</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Debit</span>',
                'source' => ucfirst($tx->source),
                'amount' => '₹' . number_format($tx->amount, 2),
                'description' => $tx->description ?? '-',
                'raw_date' => $tx->created_at->toDateTimeString()
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function creditIndex()
    {
        $user = Auth::user();
        $ca = CreditAccount::firstOrCreate(['user_id' => $user->id], [
            'credit_limit' => 5000,
            'used_credit' => 0,
            'available_credit' => 5000,
            'approval_status' => 'pending',
        ]);

        return view('credit.history', [
            'page' => 'credit_history',
            'creditAccount' => $ca
        ]);
    }

    public function creditData(Request $request)
    {
        $user = Auth::user();
        $ca = CreditAccount::firstOrCreate(['user_id' => $user->id]);
        
        $query = CreditTransaction::where('credit_account_id', $ca->id);

        // Total count
        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%")
                  ->orWhere('source', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Sorting
        $columns = ['created_at', 'type', 'source', 'amount', 'description'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $transactions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($transactions as $tx) {
            $data[] = [
                'date' => $tx->created_at->format('M d, Y H:i'),
                'type' => $tx->type === 'credit' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Repayment/Limit</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Usage</span>',
                'source' => ucfirst($tx->source),
                'amount' => '₹' . number_format($tx->amount, 2),
                'description' => $tx->description ?? '-',
                'raw_date' => $tx->created_at->toDateTimeString()
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function penaltyIndex()
    {
        $user = Auth::user();
        $totalPenalties = \App\Models\Penalty::where('user_id', $user->id)->sum('amount');
        $unpaidPenalties = \App\Models\Penalty::where('user_id', $user->id)->where('status', 'unpaid')->sum('amount');
        $paidPenalties = \App\Models\Penalty::where('user_id', $user->id)->where('status', 'paid')->sum('amount');

        return view('credit.penalties-history', [
            'page' => 'penalty_history',
            'totalPenalties' => $totalPenalties,
            'unpaidPenalties' => $unpaidPenalties,
            'paidPenalties' => $paidPenalties
        ]);
    }

    public function penaltyData(Request $request)
    {
        $user = Auth::user();
        $query = \App\Models\Penalty::where('user_id', $user->id);

        // Total count
        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Sorting
        $columns = ['created_at', 'amount', 'status', 'emi_schedule_id', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $penalties = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($penalties as $p) {
            $data[] = [
                'date' => $p->created_at->format('M d, Y H:i'),
                'amount' => '₹' . number_format($p->amount, 2),
                'status' => $p->status === 'paid' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Paid</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Unpaid</span>',
                'emi' => 'EMI #' . $p->emi_schedule_id,
                'action' => $p->status === 'unpaid' 
                    ? '<form action="'.route('credit.penalties.pay', $p->id).'" method="POST" onsubmit="return confirmSubmit(event, \'Pay this penalty?\', \'Are you sure you want to pay this penalty from your wallet?\')">' . csrf_field() . '<button type="submit" class="text-xs text-brand-500 hover:underline">Pay Now</button></form>'
                    : '-',
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }
}
