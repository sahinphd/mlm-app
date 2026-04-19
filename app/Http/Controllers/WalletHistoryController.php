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

        return view('wallet.history', [
            'page' => 'wallet_history',
            'wallet' => $wallet
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
        $columns = ['created_at', 'type', 'source', 'amount', 'description', 'created_at'];
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
        $columns = ['created_at', 'type', 'source', 'amount', 'description', 'created_at'];
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
}
