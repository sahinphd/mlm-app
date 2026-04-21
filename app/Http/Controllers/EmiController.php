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
        return view('credit.emis', [
            'page' => 'emi_schedule'
        ]);
    }

    public function emiData(Request $request)
    {
        $user = Auth::user();
        $query = EmiSchedule::where('user_id', $user->id);

        // Total count
        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhere('order_id', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // Filters
        if ($orderId = $request->input('order_id')) {
            $query->where('order_id', $orderId);
        }
        if ($emiId = $request->input('emi_id')) {
            $query->where('id', $emiId);
        }
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('due_date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('due_date', '<=', $endDate);
        }

        $totalFiltered = $query->count();

        // Sorting
        $columns = ['due_date', 'order_id', 'installment_amount', 'status', 'id'];
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'due_date';
        $query->orderBy($orderColumn, $orderDir);

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $emis = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($emis as $emi) {
            $statusHtml = '';
            if ($emi->status === 'paid') {
                $statusHtml = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400">Paid</span>';
            } elseif ($emi->status === 'overdue') {
                $statusHtml = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">Overdue</span>';
            } else {
                $statusHtml = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">Pending</span>';
            }

            $actionHtml = '-';
            if ($emi->status !== 'paid') {
                $actionHtml = '<form action="'.route('credit.emis.pay', $emi->id).'" method="POST" onsubmit="return confirm(\'Are you sure you want to pay this EMI from your main wallet?\')">' . csrf_field() . '<button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary py-1 px-3 text-center text-xs font-medium text-white hover:bg-opacity-90">Pay Now</button></form>';
            }

            $data[] = [
                'due_date' => \Carbon\Carbon::parse($emi->due_date)->format('M d, Y'),
                'order_id' => '#' . $emi->order_id,
                'amount' => '₹' . number_format($emi->installment_amount, 2),
                'status' => $statusHtml,
                'action' => $actionHtml,
                'emi_id' => 'EMI #' . $emi->id
            ];
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
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
