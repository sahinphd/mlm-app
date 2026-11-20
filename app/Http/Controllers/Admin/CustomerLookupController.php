<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerService;
use App\Services\MLMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLookupController extends Controller
{
    protected MLMService $mlmService;
    protected LedgerService $ledgerService;

    public function __construct(MLMService $mlmService, LedgerService $ledgerService)
    {
        $this->mlmService = $mlmService;
        $this->ledgerService = $ledgerService;
    }
    /**
     * Ensure the authenticated user has admin privileges.
     */
    protected function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Display the main Customer Lookup page.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $initialCustomer = null;
        $initialTransactions = null;
        $initialPagination = null;
        $searchQuery = trim((string) $request->query('search', $request->query('q', '')));
        $userId = $request->query('user_id');

        if (!empty($userId)) {
            $user = User::with(['wallet', 'creditAccount', 'referralRecord'])->find($userId);
        } elseif (!empty($searchQuery)) {
            $user = $this->findUserByQuery($searchQuery);
        } else {
            $user = null;
        }

        if ($user) {
            $initialCustomer = $this->formatCustomerData($user);
            $txData = $this->getCustomerTransactions($user, $request);
            $initialTransactions = $txData['data'];
            $initialPagination = $txData['pagination'];
        }

        return view('admin.customer_lookup.index', compact(
            'initialCustomer',
            'initialTransactions',
            'initialPagination',
            'searchQuery'
        ));
    }

    /**
     * Search for a customer via AJAX by ID, email, or mobile.
     * Supports both direct lookup and key-up live suggestions.
     */
    public function search(Request $request)
    {
        $this->ensureAdmin();

        // 1. Direct lookup by user_id
        if ($userId = $request->input('user_id')) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer with ID #' . $userId . ' not found.',
                ], 404);
            }

            $customer = $this->formatCustomerData($user);
            $txData = $this->getCustomerTransactions($user, $request);

            return response()->json([
                'success' => true,
                'customer' => $customer,
                'transactions' => $txData['data'],
                'pagination' => $txData['pagination'],
            ]);
        }

        $query = trim((string) $request->input('q', $request->input('search', '')));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a Customer ID, Email address, or Mobile number to search.',
                'matches' => [],
            ], 422);
        }

        // Live autocomplete matches for key-up
        $matches = $this->searchUsersList($query);

        // If explicitly requested as key-up suggestion
        if ($request->input('suggest') == 1) {
            return response()->json([
                'success' => true,
                'matches' => $matches,
            ]);
        }

        // If looking for primary customer
        $user = $this->findUserByQuery($query);

        // Fallback to first match if exact findUserByQuery returned null
        if (!$user && !empty($matches)) {
            $user = User::find($matches[0]['id']);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No customer found matching "' . $query . '". Please verify the Customer ID, Email, or Mobile Number.',
                'matches' => [],
            ], 404);
        }

        $customer = $this->formatCustomerData($user);
        $txData = $this->getCustomerTransactions($user, $request);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'transactions' => $txData['data'],
            'pagination' => $txData['pagination'],
            'matches' => $matches,
        ]);
    }

    /**
     * Search matching users list for live key-up autocomplete.
     */
    protected function searchUsersList(string $query): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $query);
        $isNumeric = (ctype_digit($query) || (is_numeric($query) && (int) $query > 0));

        $userQuery = User::query()
            ->with(['wallet', 'referralRecord'])
            ->where(function($q) use ($query, $cleanPhone, $isNumeric) {
                if ($isNumeric) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('name', 'LIKE', "%{$query}%");

                if (!empty($cleanPhone) && strlen($cleanPhone) >= 3) {
                    $q->orWhere('phone', 'LIKE', "%{$cleanPhone}%");
                    if (strlen($cleanPhone) >= 10) {
                        $last10 = substr($cleanPhone, -10);
                        $q->orWhere('phone', 'LIKE', "%{$last10}%");
                    }
                }

                $q->orWhereExists(function($sub) use ($query) {
                    $sub->select(DB::raw(1))
                        ->from('referrals')
                        ->whereColumn('referrals.user_id', 'users.id')
                        ->where('referrals.referral_code', 'LIKE', "%{$query}%");
                });
            });

        if ($isNumeric) {
            $userQuery->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [(int) $query]);
        }

        $users = $userQuery->orderBy('name')->take(8)->get();

        return $users->map(function ($u) {
            $wallet = $u->wallet;
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?: 'No mobile',
                'status' => $u->status ?? 'active',
                'avatar_url' => $u->avatar_url,
                'main_balance' => (float) ($wallet->main_balance ?? 0),
                'main_balance_formatted' => '₹' . number_format($wallet->main_balance ?? 0, 2),
                'referral_code' => $u->referralRecord?->referral_code ?: '',
            ];
        })->toArray();
    }

    /**
     * Fetch paginated transactions for a customer.
     */
    public function transactions(Request $request, User $user)
    {
        $this->ensureAdmin();

        $txData = $this->getCustomerTransactions($user, $request);

        return response()->json([
            'success' => true,
            'transactions' => $txData['data'],
            'pagination' => $txData['pagination'],
        ]);
    }

    /**
     * Add wallet money (Credit) to the customer.
     */
    public function addMoney(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:10000000',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $admin = Auth::user();

        try {
            $this->ledgerService->recordAdminAddMoney(
                $user,
                $amount,
                $validated['note'] ?? null,
                $admin
            );

            $updatedCustomer = $this->formatCustomerData($user);
            $txData = $this->getCustomerTransactions($user, $request);

            return response()->json([
                'success' => true,
                'message' => 'Successfully added ₹' . number_format($amount, 2) . ' to ' . $user->name . "'s wallet.",
                'customer' => $updatedCustomer,
                'transactions' => $txData['data'],
                'pagination' => $txData['pagination'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add wallet money: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deduct shop purchase amount (Debit) from customer's wallet
     * and distribute upline commissions (repurchase & BV) via MLMService.
     */
    public function shopDeduct(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:10000000',
            'bv' => 'nullable|numeric|min:0|max:10000000',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $bv = (float) ($validated['bv'] ?? 0);
        $admin = Auth::user();

        try {
            $order = DB::transaction(function () use ($user, $amount, $bv, $validated, $admin) {
                // Check balance with lock
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet || (float) $wallet->main_balance < $amount) {
                    $curBal = $wallet ? (float) $wallet->main_balance : 0;
                    throw new \RuntimeException('Insufficient wallet balance. Current balance is ₹' . number_format($curBal, 2) . ', cannot deduct ₹' . number_format($amount, 2) . '.');
                }

                // Create completed Order record for this shop purchase
                $order = Order::create([
                    'user_id' => $user->id,
                    'total_amount' => $amount,
                    'total_bv' => $bv,
                    'payment_method' => 'main_wallet',
                    'status' => 'completed',
                ]);

                // Record atomic deduction and immutable ledger
                $this->ledgerService->recordAdminShopDeduct(
                    $user,
                    $amount,
                    $order,
                    $validated['note'] ?? null,
                    $admin
                );

                // Distribute upline repurchase & BV commissions
                $this->mlmService->distributeOrderCommissions($order);

                return $order;
            });

            // Check how many upline commissions were distributed
            $commCount = \App\Models\Commission::where('order_id', $order->id)->where('amount', '>', 0)->count();

            $updatedCustomer = $this->formatCustomerData($user);
            $txData = $this->getCustomerTransactions($user, $request);

            $msg = 'Successfully deducted ₹' . number_format($amount, 2) . ' from ' . $user->name . "'s wallet (Order #" . $order->id . ").";
            if ($commCount > 0) {
                $msg .= ' Upline repurchase commission credited to ' . $commCount . ' sponsor level(s).';
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'order_id' => $order->id,
                'commissions_count' => $commCount,
                'customer' => $updatedCustomer,
                'transactions' => $txData['data'],
                'pagination' => $txData['pagination'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Find a user by query matching ID, email, or mobile.
     */
    protected function findUserByQuery(string $query): ?User
    {
        // 0. Try QR payload resolver (e.g. CUST:15, CUST-10015, plain 15)
        if ($qrUserId = $this->ledgerService->parseQrPayload($query)) {
            $user = User::find($qrUserId);
            if ($user) return $user;
        }

        // 1. Try exact ID if numeric
        if (ctype_digit($query) || (is_numeric($query) && (int) $query > 0)) {
            $user = User::where('id', (int) $query)->first();
            if ($user) return $user;
        }

        // 2. Try exact or partial email
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', strtolower($query))->first();
            if ($user) return $user;
        }

        // 3. Try phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $query);
        if (!empty($cleanPhone) && strlen($cleanPhone) >= 4) {
            $user = User::wherePhone($query)->first();
            if ($user) return $user;
        }

        // 4. Try general search scope (searches id, name, email, phone, referral code)
        return User::search($query)->first();
    }

    /**
     * Format customer details for view/JSON payload.
     */
    protected function formatCustomerData(User $user): array
    {
        $wallet = $user->wallet ?? $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]
        );
        $creditAccount = $user->creditAccount;

        // Generate initials for avatar badge (e.g. "VC" for Vatsala Choudhary)
        $words = preg_split('/\s+/', trim((string) $user->name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) $initials .= strtoupper($w[0]);
            if (strlen($initials) >= 2) break;
        }
        $initials = $initials ?: 'CU';

        // Khatabook style ID: CUST-10524
        $custId = 'CUST-' . (10000 + (int) $user->id);

        return [
            'id' => $user->id,
            'cust_id' => $custId,
            'qr_payload' => $this->ledgerService->getCustomerQrPayload($user),
            'initials' => $initials,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?: 'Not provided',
            'has_phone' => !empty($user->phone),
            'role' => $user->role,
            'status' => $user->status ?? 'active',
            'is_active' => ($user->status ?? 'active') === 'active',
            'kyc_status' => $user->kyc_status ?? 'unfilled',
            'avatar_url' => $user->avatar_url,
            'referral_code' => $user->referralRecord?->referral_code ?: 'N/A',
            'main_balance' => (float) ($wallet->main_balance ?? 0),
            'main_balance_formatted' => '₹ ' . number_format($wallet->main_balance ?? 0, 0),
            'main_balance_decimal' => '₹ ' . number_format($wallet->main_balance ?? 0, 2),
            'earning_balance' => (float) ($wallet->earning_balance ?? 0),
            'earning_balance_formatted' => '₹ ' . number_format($wallet->earning_balance ?? 0, 2),
            'credit_balance' => (float) ($wallet->credit_balance ?? 0),
            'credit_balance_formatted' => '₹ ' . number_format($wallet->credit_balance ?? 0, 2),
            'available_credit' => (float) ($creditAccount?->available_credit ?? 0),
            'available_credit_formatted' => '₹ ' . number_format($creditAccount?->available_credit ?? 0, 2),
            'credit_limit' => (float) ($creditAccount?->credit_limit ?? 0),
            'created_at' => $user->created_at ? $user->created_at->format('d M Y') : 'N/A',
        ];
    }

    /**
     * Retrieve paginated transactions for a customer with Khatabook ledger running balances.
     */
    protected function getCustomerTransactions(User $user, Request $request): array
    {
        $wallet = $user->wallet;

        if (!$wallet) {
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0,
                ],
            ];
        }

        // Calculate exact running balance chronologically for every transaction
        $allTxs = WalletTransaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'type', 'amount']);

        $running = 0;
        $runningBalances = [];
        foreach ($allTxs as $t) {
            if ($t->type === 'credit') {
                $running += (float) $t->amount;
            } else {
                $running -= (float) $t->amount;
            }
            $runningBalances[$t->id] = $running;
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 5) $perPage = 5;
        if ($perPage > 100) $perPage = 100;

        $typeFilter = $request->input('type');
        $dateFilter = $request->input('date');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $txSearch = trim((string) $request->input('search_tx', $request->input('tx_search', '')));

        $query = WalletTransaction::where('wallet_id', $wallet->id);

        if (!empty($typeFilter) && in_array($typeFilter, ['credit', 'debit'])) {
            $query->where('type', $typeFilter);
        }

        // Date range filters
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        } elseif ($dateFilter === 'this_week') {
            $query->where('created_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($dateFilter === 'this_month') {
            $query->where('created_at', '>=', Carbon::now()->startOfMonth());
        } elseif (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Text search filter within transactions
        if (!empty($txSearch)) {
            $query->where(function($q) use ($txSearch) {
                $q->where('description', 'LIKE', "%{$txSearch}%")
                  ->orWhere('reference_id', 'LIKE', "%{$txSearch}%")
                  ->orWhere('source', 'LIKE', "%{$txSearch}%");
            });
        }

        $paginator = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $items = collect($paginator->items())->map(function ($tx) use ($runningBalances) {
            $bal = $tx->balance !== null ? (float) $tx->balance : ($runningBalances[$tx->id] ?? 0);
            $isCredit = ($tx->type === 'credit');

            // Clean Khatabook transaction ID e.g. T-88103
            $displayTxnId = 'T-' . (88000 + (int) $tx->id);

            // Clean description
            $desc = $tx->description;
            if (empty($desc)) {
                $desc = $isCredit ? 'Add Wallet Money' : 'Shop Amount';
            }

            return [
                'id' => $tx->id,
                'txn_id' => $displayTxnId,
                'reference_id' => $tx->reference_id,
                'date' => $tx->created_at ? $tx->created_at->format('d M y') : 'N/A',
                'date_full' => $tx->created_at ? $tx->created_at->format('d M Y, h:i A') : 'N/A',
                'type' => $isCredit ? 'Credit' : 'Debit',
                'is_credit' => $isCredit,
                'source' => $tx->source ?: 'manual',
                'amount' => (float) $tx->amount,
                'amount_formatted' => ($isCredit ? '+ ₹ ' : '- ₹ ') . number_format($tx->amount, 0),
                'amount_decimal' => ($isCredit ? '+ ₹ ' : '- ₹ ') . number_format($tx->amount, 2),
                'balance' => $bal,
                'balance_formatted' => '₹ ' . number_format($bal, 0),
                'balance_decimal' => '₹ ' . number_format($bal, 2),
                'description' => $desc,
                'status' => 'Completed',
            ];
        })->toArray();

        return [
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem() ?: 0,
                'to' => $paginator->lastItem() ?: 0,
            ],
        ];
    }
}
