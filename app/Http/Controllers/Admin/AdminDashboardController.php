<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BvCommission;
use App\Models\Commission;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\EmiSchedule;
use App\Models\Order;
use App\Models\PaymentRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Resolves the start and end dates from user input filter.
     */
    protected function resolveFilterDates(Request $request): array
    {
        $filter = $request->query('filter', 'all');
        $startDate = null;
        $endDate = null;
        $filterLabel = 'All-Time Total';
        $startDateFormatted = '';
        $endDateFormatted = '';

        switch ($filter) {
            case 'today':
            case 'daily':
                $dateParam = $request->query('date', now()->toDateString());
                $startDate = Carbon::parse($dateParam)->startOfDay();
                $endDate = Carbon::parse($dateParam)->endOfDay();
                $filterLabel = 'Today (' . $startDate->format('M d, Y') . ')';
                $startDateFormatted = $startDate->toDateString();
                $endDateFormatted = $endDate->toDateString();
                $filter = 'today';
                break;

            case 'weekly':
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                $filterLabel = 'This Week (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
                $startDateFormatted = $startDate->toDateString();
                $endDateFormatted = $endDate->toDateString();
                $filter = 'weekly';
                break;

            case 'monthly':
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                $filterLabel = 'This Month (' . $startDate->format('F Y') . ')';
                $startDateFormatted = $startDate->toDateString();
                $endDateFormatted = $endDate->toDateString();
                $filter = 'monthly';
                break;

            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
                    $endDate = Carbon::parse($request->query('end_date'))->endOfDay();
                    $filterLabel = 'Custom Range (' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y') . ')';
                    $startDateFormatted = $startDate->toDateString();
                    $endDateFormatted = $endDate->toDateString();
                } else {
                    $startDate = now()->subDays(6)->startOfDay();
                    $endDate = now()->endOfDay();
                    $filterLabel = 'Last 7 Days (' . $startDate->format('M d') . ' - ' . $endDate->format('M d, Y') . ')';
                    $startDateFormatted = $startDate->toDateString();
                    $endDateFormatted = $endDate->toDateString();
                }
                $filter = 'custom';
                break;

            case 'all':
            default:
                $filter = 'all';
                $startDate = null;
                $endDate = null;
                $filterLabel = 'All-Time Total';
                break;
        }

        return [
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterLabel' => $filterLabel,
            'startDateFormatted' => $startDateFormatted,
            'endDateFormatted' => $endDateFormatted,
        ];
    }

    /**
     * Display the main Admin Dashboard with date filters and the Shop Initial Balance report card.
     */
    public function index(Request $request)
    {
        $filterData = $this->resolveFilterDates($request);
        $filter = $filterData['filter'];
        $startDate = $filterData['startDate'];
        $endDate = $filterData['endDate'];
        $filterLabel = $filterData['filterLabel'];
        $startDateFormatted = $filterData['startDateFormatted'];
        $endDateFormatted = $filterData['endDateFormatted'];

        // Helper to conditionally apply date filter
        $applyDate = function ($query, $column = 'created_at') use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween($column, [$startDate, $endDate]);
            }
            return $query;
        };

        // 1. Core KPIs
        $allUsersCount = User::count();
        $usersCount = $applyDate(User::query())->count();

        $allOrdersCount = Order::count();
        $ordersCount = $applyDate(Order::query())->count();

        $walletTotal = Wallet::selectRaw('COALESCE(SUM(COALESCE(main_balance,0) + COALESCE(earning_balance,0) + COALESCE(credit_balance,0)),0) as total')->value('total');

        // 2. Revenue & Fulfillment (Sales Stats)
        $salesStatsQuery = Order::query();
        $applyDate($salesStatsQuery);

        $salesStats = $salesStatsQuery->selectRaw("
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as value_completed,
            SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as value_delivered,
            SUM(CASE WHEN status = 'shipped' THEN total_amount ELSE 0 END) as value_shipped,
            SUM(CASE WHEN status = 'processing' THEN total_amount ELSE 0 END) as value_processing,
            SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as value_pending,
            SUM(CASE WHEN status = 'returned' THEN total_amount ELSE 0 END) as value_returned,
            SUM(CASE WHEN status = 'failed' THEN total_amount ELSE 0 END) as value_failed,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as count_completed,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as count_delivered,
            SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as count_shipped,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as count_processing,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as count_pending,
            SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as count_returned
        ")->first();

        $ordersByStatus = [
            'pending' => (int) ($salesStats->count_pending ?? 0),
            'processing' => (int) ($salesStats->count_processing ?? 0),
            'shipped' => (int) ($salesStats->count_shipped ?? 0),
            'delivered' => (int) ($salesStats->count_delivered ?? 0),
            'completed' => (int) ($salesStats->count_completed ?? 0),
            'returned' => (int) ($salesStats->count_returned ?? 0),
        ];

        // 3. Shopping Volumes (Gross Shopping & Credit Shopping)
        $totalShoppingDone = (float) $applyDate(Order::query())->sum('total_amount');
        $allTimeShoppingDone = (float) Order::sum('total_amount');

        $creditShoppingSql = "CASE 
            WHEN payment_method = 'credit_wallet' THEN total_amount 
            WHEN payment_method = 'split' THEN COALESCE(credit_amount, 0) 
            ELSE 0 
        END";

        $totalCreditShopping = (float) $applyDate(Order::query())->sum(DB::raw($creditShoppingSql));
        $allTimeCreditShopping = (float) Order::sum(DB::raw($creditShoppingSql));

        // 4. Shop Initial Balance & System Credit Pool
        $settings = SettingController::getSystemSettings();
        $shopInitialBalance = (float) ($settings['shop_initial_balance'] ?? 1000000);

        $creditAccountStats = CreditAccount::where('approval_status', 'approved')
            ->selectRaw('COALESCE(SUM(credit_limit), 0) as total_issued, COALESCE(SUM(used_credit), 0) as total_used')
            ->first();

        $totalCreditApproved = (float) ($creditAccountStats->total_issued ?? 0);
        $creditInMarket = (float) ($creditAccountStats->total_used ?? 0);
        $availableCreditBalance = max(0.0, $shopInitialBalance - $creditInMarket);

        $poolUtilizationPercent = $shopInitialBalance > 0
            ? min(100.0, round(($creditInMarket / $shopInitialBalance) * 100, 1))
            : 0.0;

        // Period-specific credit activity
        $periodCreditDisbursed = (float) $applyDate(CreditTransaction::where('type', 'debit')->where('source', 'purchase'))->sum('amount');
        $periodCreditCollected = (float) $applyDate(CreditTransaction::where('type', 'credit'))->sum('amount');
        $totalCreditCollected = (float) CreditTransaction::where('type', 'credit')->sum('amount');

        if ($startDate && $endDate) {
            $overdueEmiValue = (float) EmiSchedule::where('status', 'overdue')->whereBetween('due_date', [$startDate, $endDate])->sum('installment_amount');
            $allOverdueEmiValue = (float) EmiSchedule::where('status', 'overdue')->sum('installment_amount');
        } else {
            $overdueEmiValue = (float) EmiSchedule::where('status', 'overdue')->sum('installment_amount');
            $allOverdueEmiValue = $overdueEmiValue;
        }

        // 5. User & Compliance Pipeline
        $userAccountStats = User::selectRaw("
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked_users,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users,
            SUM(CASE WHEN kyc_status = 'pending' THEN 1 ELSE 0 END) as kyc_pending
        ")->first();

        // 6. Commissions & BV
        $totalPendingWithdrawals = (float) Commission::where('status', 'pending')->where('type', '!=', 'bv')->sum('amount');
        $commissionsPaidQuery = Commission::where('status', 'withdrawn')->where('type', '!=', 'bv');
        $applyDate($commissionsPaidQuery);
        $totalWithdrawnCommission = (float) $commissionsPaidQuery->sum('amount');

        $bvIssuedQuery = BvCommission::query();
        $applyDate($bvIssuedQuery);
        $totalBvIssued = (float) $bvIssuedQuery->sum('amount');

        $bvWithdrawnQuery = BvCommission::where('status', 'withdrawn');
        $applyDate($bvWithdrawnQuery);
        $totalWithdrawnBv = (float) $bvWithdrawnQuery->sum('amount');

        // 7. Dynamic Chart Data (Signups over active period or 7 days)
        $chartStart = $startDate ? $startDate->copy() : now()->subDays(6)->startOfDay();
        $chartEnd = $endDate ? $endDate->copy() : now()->endOfDay();
        $diffDays = $chartStart->diffInDays($chartEnd);

        $labels = [];
        $data = [];

        if ($diffDays <= 45) {
            $period = CarbonPeriod::create($chartStart, '1 day', $chartEnd);
            foreach ($period as $d) {
                $labels[] = $d->format('M d');
                $data[] = User::whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])->count();
            }
        } else {
            // Group into 7-day buckets if date span is long
            $period = CarbonPeriod::create($chartStart, '7 days', $chartEnd);
            foreach ($period as $d) {
                $dEnd = $d->copy()->addDays(6)->endOfDay();
                if ($dEnd > $chartEnd) $dEnd = $chartEnd;
                $labels[] = $d->format('M d') . ' - ' . $dEnd->format('M d');
                $data[] = User::whereBetween('created_at', [$d->copy()->startOfDay(), $dEnd])->count();
            }
        }

        // 8. Recent Projects & Payments
        $projects = Project::orderBy('id', 'asc')->take(12)->get()->map(function ($p) {
            return [
                'title' => $p->title,
                'subtitle' => $p->subtitle,
                'start' => $p->start_date?->format('M d') ?? '-',
                'end' => $p->end_date?->format('M d') ?? '-',
                'progress' => (int) $p->progress,
                'status' => $p->status,
                'avatars' => [],
            ];
        })->toArray();

        $recentPaymentsQuery = PaymentRequest::with('user');
        $applyDate($recentPaymentsQuery);
        $recentPayments = $recentPaymentsQuery->orderBy('created_at', 'desc')->take(6)->get();

        $recentOrdersQuery = Order::with('user');
        $applyDate($recentOrdersQuery);
        $recentOrders = $recentOrdersQuery->orderBy('created_at', 'desc')->take(6)->get();

        $currencyCode = $settings['currency'] ?? 'INR';
        $currencySymbols = [
            'INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$'
        ];
        $symbol = $currencySymbols[$currencyCode] ?? $currencyCode;

        return view('admin.dashboard', compact(
            'filter',
            'filterLabel',
            'startDateFormatted',
            'endDateFormatted',
            'allUsersCount',
            'usersCount',
            'allOrdersCount',
            'ordersCount',
            'walletTotal',
            'salesStats',
            'ordersByStatus',
            'shopInitialBalance',
            'creditInMarket',
            'availableCreditBalance',
            'totalCreditApproved',
            'poolUtilizationPercent',
            'totalShoppingDone',
            'allTimeShoppingDone',
            'totalCreditShopping',
            'allTimeCreditShopping',
            'periodCreditDisbursed',
            'periodCreditCollected',
            'creditAccountStats',
            'totalCreditCollected',
            'overdueEmiValue',
            'allOverdueEmiValue',
            'userAccountStats',
            'totalPendingWithdrawals',
            'totalWithdrawnCommission',
            'totalBvIssued',
            'totalWithdrawnBv',
            'labels',
            'data',
            'projects',
            'recentPayments',
            'recentOrders',
            'symbol'
        ));
    }

    /**
     * Export a CSV report summarizing the dashboard metrics for the chosen date range.
     */
    public function exportSummary(Request $request)
    {
        $filterData = $this->resolveFilterDates($request);
        $filter = $filterData['filter'];
        $startDate = $filterData['startDate'];
        $endDate = $filterData['endDate'];
        $filterLabel = $filterData['filterLabel'];

        $applyDate = function ($query, $column = 'created_at') use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween($column, [$startDate, $endDate]);
            }
            return $query;
        };

        // Fetch settings & capital pool
        $settings = SettingController::getSystemSettings();
        $shopInitialBalance = (float) ($settings['shop_initial_balance'] ?? 1000000);

        $creditAccountStats = CreditAccount::where('approval_status', 'approved')
            ->selectRaw('COALESCE(SUM(credit_limit), 0) as total_issued, COALESCE(SUM(used_credit), 0) as total_used')
            ->first();

        $totalCreditApproved = (float) ($creditAccountStats->total_issued ?? 0);
        $creditInMarket = (float) ($creditAccountStats->total_used ?? 0);
        $availableCreditBalance = max(0.0, $shopInitialBalance - $creditInMarket);
        $poolUtilization = $shopInitialBalance > 0 ? round(($creditInMarket / $shopInitialBalance) * 100, 2) . '%' : '0%';

        // Orders & Shopping
        $totalOrdersCount = $applyDate(Order::query())->count();
        $totalShoppingDone = (float) $applyDate(Order::query())->sum('total_amount');
        $creditShoppingSql = "CASE WHEN payment_method = 'credit_wallet' THEN total_amount WHEN payment_method = 'split' THEN COALESCE(credit_amount, 0) ELSE 0 END";
        $totalCreditShopping = (float) $applyDate(Order::query())->sum(DB::raw($creditShoppingSql));

        // Sales breakdowns
        $salesStatsQuery = Order::query();
        $applyDate($salesStatsQuery);
        $sales = $salesStatsQuery->selectRaw("
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as val_completed,
            SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as val_delivered,
            SUM(CASE WHEN status = 'shipped' THEN total_amount ELSE 0 END) as val_shipped,
            SUM(CASE WHEN status = 'processing' THEN total_amount ELSE 0 END) as val_processing,
            SUM(CASE WHEN status = 'pending' THEN total_amount ELSE 0 END) as val_pending,
            SUM(CASE WHEN status = 'returned' THEN total_amount ELSE 0 END) as val_returned
        ")->first();

        // Users & Wallets
        $usersCount = $applyDate(User::query())->count();
        $walletTotal = Wallet::selectRaw('COALESCE(SUM(COALESCE(main_balance,0) + COALESCE(earning_balance,0) + COALESCE(credit_balance,0)),0) as total')->value('total');

        // Repayments & Overdue
        $periodRepayments = (float) $applyDate(CreditTransaction::where('type', 'credit'))->sum('amount');
        $overdueEmi = (float) EmiSchedule::where('status', 'overdue')->sum('installment_amount');

        // CSV Rows
        $rows = [
            ['Category', 'Metric', 'Value', 'Notes / Context'],
            ['Filter Overview', 'Selected Date Range', $filterLabel, 'Period applied to range-sensitive metrics'],
            ['Filter Overview', 'Export Generated At', now()->format('Y-m-d H:i:s'), 'Admin Export'],
            ['', '', '', ''],
            ['Shop Credit Capital', 'Shop Initial Balance (Maximum Allowable Limit)', number_format($shopInitialBalance, 2), 'Managed from Admin Settings'],
            ['Shop Credit Capital', 'Credit in the Market (Total Used Credit)', number_format($creditInMarket, 2), 'Currently outstanding credit used by shoppers'],
            ['Shop Credit Capital', 'Available Credit Pool Balance', number_format($availableCreditBalance, 2), 'Remaining capital pool (Initial Balance - Market Credit)'],
            ['Shop Credit Capital', 'Total Approved Credit Limit (Granted Ceiling)', number_format($totalCreditApproved, 2), 'Sum of credit limits granted to approved users'],
            ['Shop Credit Capital', 'Credit Pool Utilization Rate', $poolUtilization, 'Ratio of Market Credit to Initial Balance'],
            ['', '', '', ''],
            ['Shopping Performance', 'Total Shopping Done (Gross Sales in Period)', number_format($totalShoppingDone, 2), 'Total order turnover during selected period'],
            ['Shopping Performance', 'Shopping Done on Credit (in Period)', number_format($totalCreditShopping, 2), 'Orders financed via Credit Wallet or Split Payment'],
            ['Shopping Performance', 'Total Orders Count (in Period)', $totalOrdersCount, 'Orders placed in selected period'],
            ['Shopping Performance', 'Completed Sales Value', number_format($sales->val_completed ?? 0, 2), 'Orders with completed status'],
            ['Shopping Performance', 'Delivered Sales Value', number_format($sales->val_delivered ?? 0, 2), 'Orders with delivered status'],
            ['Shopping Performance', 'In-Shipping Sales Value', number_format($sales->val_shipped ?? 0, 2), 'Orders currently shipped'],
            ['Shopping Performance', 'Processing Orders Value', number_format($sales->val_processing ?? 0, 2), 'Orders under processing'],
            ['Shopping Performance', 'Pending Orders Value', number_format($sales->val_pending ?? 0, 2), 'Orders awaiting fulfillment/payment'],
            ['Shopping Performance', 'Returned Orders Value', number_format($sales->val_returned ?? 0, 2), 'Orders returned/refunded'],
            ['', '', '', ''],
            ['Collections & Repayments', 'Credit Repayments Collected (in Period)', number_format($periodRepayments, 2), 'Realized loan/credit repayments in period'],
            ['Collections & Repayments', 'Overdue EMI Value', number_format($overdueEmi, 2), 'Current overdue EMI installments'],
            ['', '', '', ''],
            ['Users & Wallets', 'User Registrations (in Period)', $usersCount, 'New signups within period'],
            ['Users & Wallets', 'Total Wallet Balance Liability', number_format($walletTotal, 2), 'Main + Earning + Credit wallet balances across all users'],
        ];

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function ($val) {
                return '"' . str_replace('"', '""', (string) $val) . '"';
            }, $row)) . "\n";
        }

        $filename = 'admin_report_' . $filter . '_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
