<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AdminWalletHistoryController extends Controller
{
    protected function ensureAdmin()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    public function walletIndex()
    {
        $this->ensureAdmin();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.wallet.history', compact('users'));
    }

    public function walletData(Request $request)
    {
        $this->ensureAdmin();
        
        $query = WalletTransaction::with('wallet.user');

        // Total count
        $totalData = $query->count();

        // Filters
        if ($userId = $request->input('user_id')) {
            $query->whereHas('wallet', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('wallet.user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        $columns = ['created_at', 'wallet_id', 'type', 'source', 'amount', 'description', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        
        if ($orderColumn === 'wallet_id') {
            // Sorting by user name requires a join or complex query, for now default to date
            $query->orderBy('created_at', $orderDir);
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $transactions = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($transactions as $tx) {
            $data[] = [
                'date' => $tx->created_at->format('M d, Y H:i'),
                'user' => ($tx->wallet->user->name ?? 'N/A') . '<br><small class="text-gray-500">' . ($tx->wallet->user->email ?? '') . '</small>',
                'type' => $tx->type === 'credit' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Credit</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Debit</span>',
                'source' => ucfirst($tx->source),
                'amount' => '₹' . number_format($tx->amount, 2),
                'description' => $tx->description ?? '-',
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
        $this->ensureAdmin();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.credit.history', compact('users'));
    }

    public function creditData(Request $request)
    {
        $this->ensureAdmin();
        
        $query = CreditTransaction::with('creditAccount.user');

        // Total count
        $totalData = $query->count();

        // Filters
        if ($userId = $request->input('user_id')) {
            $query->whereHas('creditAccount', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('creditAccount.user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        $columns = ['created_at', 'credit_account_id', 'type', 'source', 'amount', 'description', 'created_at'];
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
                'user' => ($tx->creditAccount->user->name ?? 'N/A') . '<br><small class="text-gray-500">' . ($tx->creditAccount->user->email ?? '') . '</small>',
                'type' => $tx->type === 'credit' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Repayment/Limit</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Usage</span>',
                'source' => ucfirst($tx->source),
                'amount' => '₹' . number_format($tx->amount, 2),
                'description' => $tx->description ?? '-',
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
        $this->ensureAdmin();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.credit.penalties-history', compact('users'));
    }

    public function penaltyData(Request $request)
    {
        $this->ensureAdmin();
        
        $query = \App\Models\Penalty::with('user');

        // Total count
        $totalData = $query->count();

        // Filters
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('amount', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        $columns = ['created_at', 'user_id', 'amount', 'status', 'emi_schedule_id', 'created_at'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        
        if ($orderColumn === 'user_id') {
            $query->orderBy('created_at', $orderDir);
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $penalties = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($penalties as $p) {
            $data[] = [
                'date' => $p->created_at->format('M d, Y H:i'),
                'user' => ($p->user->name ?? 'N/A') . '<br><small class="text-gray-500">' . ($p->user->email ?? '') . '</small>',
                'amount' => '₹' . number_format($p->amount, 2),
                'status' => $p->status === 'paid' 
                    ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Paid</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Unpaid</span>',
                'emi' => 'EMI #' . $p->emi_schedule_id,
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
