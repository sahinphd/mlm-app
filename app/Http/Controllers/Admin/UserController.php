<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\MLMService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $mlmService;

    public function __construct(MLMService $mlmService)
    {
        $this->mlmService = $mlmService;
    }

    private function ensureAdmin(): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $totalUsers = User::count();

        // Creditors: Approved credit account with credit_limit > 0 or used_credit > 0
        $creditorsCount = User::whereHas('creditAccount', function($q) {
            $q->where('approval_status', 'approved')->where(function($sub) {
                $sub->where('credit_limit', '>', 0)->orWhere('used_credit', '>', 0);
            });
        })->count();

        $nonCreditorsCount = max(0, $totalUsers - $creditorsCount);

        $creditAggregates = \App\Models\CreditAccount::where('approval_status', 'approved')
            ->selectRaw('COALESCE(SUM(credit_limit), 0) as total_limit, COALESCE(SUM(used_credit), 0) as total_used, COALESCE(SUM(available_credit), 0) as total_available')
            ->first();

        $overdueStats = \App\Models\EmiSchedule::where('status', 'overdue')
            ->selectRaw('COUNT(*) as overdue_count, COALESCE(SUM(installment_amount), 0) as overdue_total')
            ->first();

        $totalShoppingAll = (float) \App\Models\Order::sum('total_amount');

        $khataSummary = [
            'total_users' => $totalUsers,
            'creditors_count' => $creditorsCount,
            'non_creditors_count' => $nonCreditorsCount,
            'total_credit_limit' => (float) ($creditAggregates->total_limit ?? 0),
            'total_credit_used' => (float) ($creditAggregates->total_used ?? 0),
            'total_available_credit' => (float) ($creditAggregates->total_available ?? 0),
            'overdue_total' => (float) ($overdueStats->overdue_total ?? 0),
            'overdue_count' => (int) ($overdueStats->overdue_count ?? 0),
            'total_shopping_done' => $totalShoppingAll,
        ];

        $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
        $currencyCode = $settings['currency'] ?? 'INR';
        $currencySymbols = [
            'INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$'
        ];
        $symbol = $currencySymbols[$currencyCode] ?? $currencyCode;

        return view('admin.users', compact('khataSummary', 'symbol'));
    }

    public function kycIndex(Request $request)
    {
        $this->ensureAdmin();
        $status = $request->query('status', 'pending');

        $stats = [
            'pending' => User::where('kyc_status', 'pending')->count(),
            'approved' => User::where('kyc_status', 'approved')->count(),
            'rejected' => User::where('kyc_status', 'rejected')->count(),
            'unfilled' => User::where('kyc_status', 'unfilled')->count(),
        ];

        $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
        $defaultCreditLimit = (float) ($settings['default_credit_limit'] ?? 5000);

        $users = User::with(['creditAccount', 'wallet', 'referralRecord.parent'])
            ->where('kyc_status', $status)
            ->orderBy('updated_at', 'desc')
            ->paginate(25);

        return view('admin.kyc_index', compact('users', 'status', 'stats', 'defaultCreditLimit'));
    }

    public function quickOnboard(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'credit_limit' => 'nullable|numeric|min:0',
            'kyc_status' => 'nullable|string|in:approved,pending,rejected',
            'status' => 'nullable|string|in:active,pending,blocked',
            'credit_approval' => 'nullable|string|in:approved,pending,rejected',
            'kyc_notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $user->status;
        $oldKycStatus = $user->kyc_status;

        $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
        $defaultLimit = (float) ($settings['default_credit_limit'] ?? 5000);
        $creditLimit = isset($validated['credit_limit']) ? (float) $validated['credit_limit'] : $defaultLimit;

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $user, $validated, $oldStatus, $creditLimit) {
            $user->kyc_status = $validated['kyc_status'] ?? 'approved';
            $user->status = $validated['status'] ?? 'active';
            if (array_key_exists('kyc_notes', $validated)) {
                $user->kyc_notes = $validated['kyc_notes'];
            }
            $user->save();

            $creditApproval = $validated['credit_approval'] ?? 'approved';

            $ca = \App\Models\CreditAccount::firstOrNew(['user_id' => $user->id]);
            $oldCreditStatus = $ca->exists ? $ca->approval_status : 'pending';

            $ca->credit_limit = $creditLimit;
            $ca->available_credit = max(0, $creditLimit - ($ca->used_credit ?? 0));
            $ca->approval_status = $creditApproval;
            $ca->save();

            if ($creditApproval === 'approved' && $oldCreditStatus !== 'approved') {
                \App\Models\CreditTransaction::create([
                    'credit_account_id' => $ca->id,
                    'type' => 'credit',
                    'amount' => $creditLimit,
                    'source' => 'manual',
                    'description' => 'Initial credit limit approved during onboarding'
                ]);
            }

            // Trigger MLM joining commission if user status is active and credit is approved
            if ($user->status === 'active' && $ca->approval_status === 'approved') {
                $this->mlmService->distributeJoiningCommissions($user->id);
            }
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User ' . $user->name . ' successfully onboarded: KYC approved, account activated, and credit limit granted!'
            ]);
        }

        return back()->with('success', 'User ' . $user->name . ' successfully onboarded: KYC approved, account activated, and credit limit granted!');
    }

    public function quickReject(Request $request, User $user)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'kyc_notes' => 'required|string|max:500',
        ]);

        $user->kyc_status = 'rejected';
        $user->kyc_notes = $validated['kyc_notes'];
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'KYC marked as rejected. The user has been notified to correct their details.'
            ]);
        }

        return back()->with('success', 'KYC marked as rejected. The user has been notified to correct their details.');
    }

    public function genealogyIndex(Request $request)
    {
        $this->ensureAdmin();
        $userId = $request->query('user_id');
        
        if ($userId) {
            $user = User::with(['referralRecord'])->withCount('referredUsers')->find($userId);
        } else {
            // Default to the first user (the root of the system)
            $user = User::with(['referralRecord'])->withCount('referredUsers')->orderBy('id', 'asc')->first();
        }

        return view('admin.genealogy.genealogy', compact('user'));
    }

    // Server-side DataTables JSON
    public function data(Request $request)
    {
        $this->ensureAdmin();

        $creditType = $request->input('credit_type', 'all');

        $columns = [
            0 => 'id',
            2 => 'name',
            3 => 'email',
            5 => 'status',
            6 => 'kyc_status',
            7 => 'credit_limit',
            11 => 'created_at'
        ];

        $query = User::with(['wallet', 'creditAccount', 'referralRecord.parent'])
            ->withSum('orders as total_shopping', 'total_amount')
            ->withSum(['emis as overdue_amount' => function($q) {
                $q->where('status', 'overdue');
            }], 'installment_amount')
            ->withSum(['commissions as total_commission' => function($q) {
                $q->where('type', '!=', 'bv');
            }], 'amount')
            ->withSum('bvCommissions as total_bv', 'amount');

        // Filter by credit type (Khatabook tab toggle)
        if ($creditType === 'creditor') {
            $query->whereHas('creditAccount', function($q) {
                $q->where('approval_status', 'approved')->where(function($sub) {
                    $sub->where('credit_limit', '>', 0)->orWhere('used_credit', '>', 0);
                });
            });
        } elseif ($creditType === 'non_creditor') {
            $query->where(function($q) {
                $q->whereDoesntHave('creditAccount')
                  ->orWhereHas('creditAccount', function($sub) {
                      $sub->where('approval_status', '!=', 'approved')
                          ->orWhere(function($zero) {
                              $zero->where(function($c) {
                                  $c->whereNull('credit_limit')->orWhere('credit_limit', '<=', 0);
                              })->where(function($u) {
                                  $u->whereNull('used_credit')->orWhere('used_credit', '<=', 0);
                              });
                          });
                  });
            });
        }

        $recordsTotal = (clone $query)->count();

        // global search
        $search = $request->input('search.value');
        if ($search) {
            $query->search($search);
        }

        $recordsFiltered = (clone $query)->count();

        // ordering
        $orderColIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColIndex])) {
            $query->orderBy($columns[$orderColIndex], $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $data = $query->skip($start)->take($length)->get();

        $settings = \App\Http\Controllers\Admin\SettingController::getSystemSettings();
        $defaultLimit = (float) ($settings['default_credit_limit'] ?? 5000);

        $rows = $data->map(function($u) use ($defaultLimit){
            $statusBadge = match($u->status) {
                'active' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Active</span>',
                'pending' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">Pending</span>',
                'blocked' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300">Blocked</span>',
                default => e($u->status)
            };

            $kycBadge = match($u->kyc_status) {
                'approved' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Approved</span>',
                'pending' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">Pending</span>',
                'rejected' => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300">Rejected</span>',
                default => '<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">Unfilled</span>'
            };

            $cs = $u->creditAccount->approval_status ?? 'N/A';
            $creditStatusBadge = match($cs) {
                'approved' => '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">Approved</span>',
                'pending' => '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">Pending</span>',
                'rejected' => '<span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300">Rejected</span>',
                default => '<span class="text-xs text-gray-400">N/A</span>'
            };

            $isCreditor = ($u->creditAccount && $u->creditAccount->approval_status === 'approved' && (($u->creditAccount->credit_limit ?? 0) > 0 || ($u->creditAccount->used_credit ?? 0) > 0));

            $khataBadge = $isCreditor
                ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-100 text-orange-800 dark:bg-orange-950/40 dark:text-orange-300 border border-orange-200 dark:border-orange-900/60 shadow-2xs whitespace-nowrap"><span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>Creditor</span>'
                : '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 whitespace-nowrap">Non-Creditor</span>';

            $creditLimit = (float) ($u->creditAccount->credit_limit ?? 0);
            $usedCredit = (float) ($u->creditAccount->used_credit ?? 0);
            $availableCredit = (float) ($u->creditAccount->available_credit ?? 0);
            $mainBalance = (float) ($u->wallet->main_balance ?? 0);
            $totalShopping = (float) ($u->total_shopping ?? 0);
            $totalCommission = (float) ($u->total_commission ?? 0);
            $totalBv = (float) ($u->total_bv ?? 0);
            $overdueAmount = (float) ($u->overdue_amount ?? 0);

            $parentName = $u->referralRecord?->parent?->name ?? 'None';
            $referralCode = $u->referralRecord?->referral_code ?? '-';
            $userLimit = (float) ($u->creditAccount->credit_limit ?? $defaultLimit);
            $canQuickOnboard = ($u->status === 'pending' || $u->kyc_status === 'pending');

            // Khatabook Circular Initials Avatar
            $nameClean = trim($u->name);
            $nameParts = preg_split('/\s+/', $nameClean);
            $initials = '';
            if (!empty($nameParts[0])) {
                $initials .= mb_substr($nameParts[0], 0, 1);
            }
            if (count($nameParts) > 1 && !empty($nameParts[count($nameParts) - 1])) {
                $initials .= mb_substr($nameParts[count($nameParts) - 1], 0, 1);
            }
            if (empty($initials)) {
                $initials = 'CU';
            }
            $initials = strtoupper($initials);

            $colorPalettes = [
                'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-200 dark:border-emerald-800',
                'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-200 dark:border-blue-800',
                'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/60 dark:text-purple-200 dark:border-purple-800',
                'bg-pink-100 text-pink-800 border-pink-300 dark:bg-pink-950/60 dark:text-pink-200 dark:border-pink-800',
                'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-200 dark:border-amber-800',
                'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-950/60 dark:text-indigo-200 dark:border-indigo-800',
            ];
            $palette = $colorPalettes[abs(crc32($u->name ?? $u->email)) % count($colorPalettes)];

            $hasCustomAvatar = !empty($u->avatar);
            $avatarImg = $hasCustomAvatar
                ? '<img src="'.e($u->avatar_url).'" alt="avatar" class="w-9 h-9 rounded-full object-cover border border-gray-200 dark:border-gray-700 shadow-2xs js-user-info cursor-pointer" data-id="'.$u->id.'" data-name="'.e($u->name).'" data-phone="'.e($u->phone ?? '-').'" data-ref-by="'.e($parentName).'" data-ref-code="'.e($referralCode).'" data-avatar="'.e($u->avatar_url).'" data-wallet="'.number_format($mainBalance, 2).'" data-commission="'.number_format($totalCommission, 2).'" data-bv="'.number_format($totalBv, 2).'" data-credit-used="'.number_format($usedCredit, 2).'" data-overdue="'.number_format($overdueAmount, 2).'" data-kyc-status="'.e($u->kyc_status).'" data-aadhaar="'.e($u->aadhaar_number ?? 'N/A').'" data-pan="'.e($u->pan_number ?? 'N/A').'" data-address="'.e($u->address ?? 'N/A').'" data-nominee="'.e(($u->nominee_name ? $u->nominee_name . ' (' . ($u->nominee_relation ?? 'Nominee') . ')' : 'N/A')).'" data-credit-limit="'.$userLimit.'" data-can-onboard="'.($canQuickOnboard ? '1' : '0').'">'
                : '<div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs border shadow-2xs cursor-pointer js-user-info '.$palette.'" data-id="'.$u->id.'" data-name="'.e($u->name).'" data-phone="'.e($u->phone ?? '-').'" data-ref-by="'.e($parentName).'" data-ref-code="'.e($referralCode).'" data-avatar="'.e($u->avatar_url).'" data-wallet="'.number_format($mainBalance, 2).'" data-commission="'.number_format($totalCommission, 2).'" data-bv="'.number_format($totalBv, 2).'" data-credit-used="'.number_format($usedCredit, 2).'" data-overdue="'.number_format($overdueAmount, 2).'" data-kyc-status="'.e($u->kyc_status).'" data-aadhaar="'.e($u->aadhaar_number ?? 'N/A').'" data-pan="'.e($u->pan_number ?? 'N/A').'" data-address="'.e($u->address ?? 'N/A').'" data-nominee="'.e(($u->nominee_name ? $u->nominee_name . ' (' . ($u->nominee_relation ?? 'Nominee') . ')' : 'N/A')).'" data-credit-limit="'.$userLimit.'" data-can-onboard="'.($canQuickOnboard ? '1' : '0').'">'.e($initials).'</div>';

            // Khatabook Compact Customer Name & Phone Cell
            $customerNameCell = '<div class="leading-tight">'
                . '<div class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">'
                . e($u->name)
                . ($u->role === 'admin' ? '<span class="text-[9px] px-1 py-0.2 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 font-bold">Admin</span>' : '')
                . '</div>'
                . ($u->phone ? '<div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5 flex items-center gap-1"><svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>'.e($u->phone).'</div>' : '<div class="text-[11px] text-gray-400">No phone</div>')
                . '</div>';

            // Used Credit (Khata Udhar Due)
            $usedCreditFormatted = $usedCredit > 0
                ? '<div class="text-right leading-tight">'
                    . '<div class="font-extrabold text-sm text-red-600 dark:text-red-400 whitespace-nowrap">Rs.' . number_format($usedCredit, 2) . '</div>'
                    . '<span class="inline-block text-[9px] font-bold uppercase tracking-wider text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/50 px-1 py-0.2 rounded">You\'ll Get</span>'
                    . '</div>'
                : '<div class="text-right leading-tight">'
                    . '<div class="font-semibold text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">Rs.0.00</div>'
                    . '<span class="text-[10px] text-gray-400">No Due</span>'
                    . '</div>';

            // Available Credit & Limit
            $availableCreditFormatted = $creditLimit > 0
                ? '<div class="text-right leading-tight">'
                    . '<div class="font-bold text-xs text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Rs.' . number_format($availableCredit, 2) . '</div>'
                    . '<span class="text-[10px] text-gray-400 dark:text-gray-500 whitespace-nowrap">Limit: Rs.' . number_format($creditLimit, 2) . '</span>'
                    . '</div>'
                : '<div class="text-right leading-tight">'
                    . '<div class="text-xs text-gray-400 whitespace-nowrap">Rs.0.00</div>'
                    . '<span class="text-[10px] text-gray-400 whitespace-nowrap">No Limit</span>'
                    . '</div>';

            // Main Balance
            $mainBalanceFormatted = '<div class="text-right font-bold text-xs text-gray-900 dark:text-white whitespace-nowrap">Rs.' . number_format($mainBalance, 2) . '</div>';

            // Shop Done
            $shopDoneFormatted = '<div class="text-right font-bold text-xs text-blue-700 dark:text-blue-300 whitespace-nowrap">Rs.' . number_format($totalShopping, 2) . '</div>';

            // Direct Quick Action Buttons
            $khataLookupBtn = '<a href="'.route('admin.customer-lookup.index', ['user_id' => $u->id]).'" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-[#1555a8] hover:bg-[#12468d] text-white rounded-lg shadow-2xs transition-colors whitespace-nowrap" title="Open Customer Khata Statement">📖 Khata</a>';

            $toggleDetailsBtn = '<button type="button" class="js-toggle-details px-2 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg transition-colors inline-flex items-center gap-1 whitespace-nowrap" title="Show More Details"><span>Details</span><svg class="w-3.5 h-3.5 transform transition-transform duration-200 detail-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>';

            $quickActions = '<div class="flex items-center justify-end gap-1.5">' . $khataLookupBtn . $toggleDetailsBtn . '</div>';

            // Rich Collapsible Child Drawer HTML (Everything else in neat collapsible child table)
            $childDrawerHtml = '
<div class="p-3 sm:p-4 bg-gray-50/90 dark:bg-gray-800/90 rounded-xl border border-gray-200 dark:border-gray-700 shadow-inner space-y-3 text-left">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Col 1: Identity & KYC -->
        <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200/80 dark:border-gray-800 space-y-1.5 text-xs">
            <div class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-[10px] flex items-center justify-between border-b pb-1 dark:border-gray-800">
                <span>KYC &amp; Verification</span>
                '.$kycBadge.'
            </div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Aadhaar:</span><span class="font-mono font-medium text-gray-800 dark:text-gray-200">'.e($u->aadhaar_number ?? 'Not Provided').'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">PAN:</span><span class="font-mono font-medium text-gray-800 dark:text-gray-200">'.e($u->pan_number ?? 'Not Provided').'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Account Status:</span>'.$statusBadge.'</div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Credit Account:</span>'.$creditStatusBadge.'</div>
            '.($u->kyc_notes ? '<div class="pt-1 text-[11px] text-gray-500 italic">Note: '.e($u->kyc_notes).'</div>' : '').'
        </div>

        <!-- Col 2: Contact & Nominee -->
        <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200/80 dark:border-gray-800 space-y-1.5 text-xs">
            <div class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-[10px] border-b pb-1 dark:border-gray-800">
                Contact &amp; Nominee
            </div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Email:</span><a href="mailto:'.e($u->email).'" class="text-blue-600 truncate max-w-[160px]">'.e($u->email).'</a></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Phone:</span><span class="font-mono text-gray-800 dark:text-gray-200">'.e($u->phone ?? 'N/A').'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Nominee:</span><span class="text-gray-800 dark:text-gray-200 truncate max-w-[150px]">'.e($u->nominee_name ? ($u->nominee_name . ' (' . ($u->nominee_relation ?? 'Nominee') . ')') : 'N/A').'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Address:</span><span class="text-gray-800 dark:text-gray-200 truncate max-w-[150px]" title="'.e($u->address ?? '').'">'.e($u->address ?? 'N/A').'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Joined:</span><span class="text-gray-700 dark:text-gray-300">'.$u->created_at?->format('d M Y').'</span></div>
        </div>

        <!-- Col 3: Network & Commissions -->
        <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200/80 dark:border-gray-800 space-y-1.5 text-xs">
            <div class="font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider text-[10px] border-b pb-1 dark:border-gray-800">
                Network &amp; Earnings
            </div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Referred By:</span><span class="font-medium text-gray-800 dark:text-gray-200">'.e($parentName).'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Referral Code:</span><span class="font-mono px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">'.e($referralCode).'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Commissions:</span><span class="font-bold text-emerald-600 dark:text-emerald-400">Rs.'.number_format($totalCommission, 2).'</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">BV Points:</span><span class="font-bold text-purple-600 dark:text-purple-400">'.number_format($totalBv, 2).' BV</span></div>
            <div class="flex justify-between py-0.5"><span class="text-gray-500">Overdue EMIs:</span><span class="font-bold '.($overdueAmount > 0 ? 'text-rose-600' : 'text-gray-400').'">Rs.'.number_format($overdueAmount, 2).'</span></div>
        </div>
    </div>

    <!-- Child Row Action Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="'.route('admin.customer-lookup.index', ['user_id' => $u->id]).'" class="px-2.5 py-1 text-xs font-bold bg-[#1555a8] text-white hover:bg-[#12468d] rounded-lg transition-colors flex items-center gap-1 shadow-2xs">
                <span>📖 Open Customer Statement</span>
            </a>
            '.($canQuickOnboard ? '
            <button type="button" class="js-quick-onboard-btn px-2.5 py-1 text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg transition-colors flex items-center gap-1 shadow-2xs"
                    data-id="'.$u->id.'" data-name="'.e($u->name).'" data-email="'.e($u->email).'" data-phone="'.e($u->phone ?? '-').'" data-aadhaar="'.e($u->aadhaar_number ?? 'N/A').'" data-pan="'.e($u->pan_number ?? 'N/A').'" data-address="'.e($u->address ?? 'N/A').'" data-nominee-name="'.e($u->nominee_name ?? 'N/A').'" data-nominee-rel="'.e($u->nominee_relation ?? 'N/A').'" data-kyc-status="'.e($u->kyc_status).'" data-status="'.e($u->status).'" data-credit-limit="'.$userLimit.'" data-upline="'.e($parentName).'" data-avatar="'.e($u->avatar_url).'">
                <span>⚡ Quick Onboard</span>
            </button>' : '').'
            <a href="'.route('admin.users.show', $u->id).'" class="px-2.5 py-1 text-xs font-semibold bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors">
                View Profile
            </a>
            '.((!$u->isAdmin()) ? '
            <form action="'.route('admin.impersonate', $u->id).'?redirect=profile" method="POST" class="inline">'.csrf_field().'
                <button type="submit" class="px-2.5 py-1 text-xs font-semibold bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 rounded-lg border border-emerald-300 dark:border-emerald-700 transition-colors">
                    Login as User
                </button>
            </form>' : '').'
            <a href="'.route('admin.users.genealogy', $u->id).'" class="px-2.5 py-1 text-xs font-semibold bg-white dark:bg-gray-700 text-purple-700 dark:text-purple-300 hover:bg-purple-50 rounded-lg border border-purple-300 dark:border-purple-700 transition-colors">
                Genealogy Tree
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="/admin/users/'.$u->id.'/edit" class="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:underline">
                Edit User
            </a>
            <span class="text-gray-300">|</span>
            <a href="#" data-id="'.$u->id.'" class="px-2.5 py-1 text-xs font-semibold text-red-600 js-delete hover:underline">
                Delete User
            </a>
        </div>
    </div>
</div>';

            // High-Performance Compact Mobile User Card HTML
            $mobileCardHtml = '
<div class="user-mobile-card bg-white dark:bg-gray-800 rounded-xl border border-gray-200/90 dark:border-gray-700 shadow-2xs hover:shadow-xs transition-all duration-200 p-2.5 sm:p-3 space-y-2" data-user-id="'.$u->id.'">
    <!-- Card Header: Avatar, Name, Phone & Khata Badge -->
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2 min-w-0">
            '.$avatarImg.'
            <div class="min-w-0">
                <div class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate flex items-center gap-1.5">
                    <span class="truncate">'.e($u->name).'</span>
                    '.($u->role === 'admin' ? '<span class="text-[9px] px-1 py-0.2 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/60 dark:text-purple-300 font-bold shrink-0">Admin</span>' : '').'
                </div>
                '.($u->phone ? '<a href="tel:'.e($u->phone).'" class="text-[11px] text-gray-500 dark:text-gray-400 font-mono flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"><svg class="w-2.5 h-2.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><span>'.e($u->phone).'</span></a>' : '<div class="text-[10px] text-gray-400">No phone</div>').'
            </div>
        </div>
        <div class="shrink-0">
            '.$khataBadge.'
        </div>
    </div>

    <!-- Symmetrical 2x2 Financial Metrics Strip -->
    <div class="grid grid-cols-2 gap-1.5 p-1.5 sm:p-2 rounded-lg bg-gray-50/80 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700/60 text-xs">
        <!-- Metric 1: Khata Due (Udhar) -->
        <div class="flex flex-col min-w-0">
            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">Khata Due (Udhar)</span>
            <div class="mt-0.5 flex items-baseline gap-1 flex-wrap">
                <span class="font-extrabold text-xs sm:text-sm '.($usedCredit > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400').'">Rs.'.number_format($usedCredit, 2).'</span>
                '.($usedCredit > 0 ? '<span class="text-[9px] bg-red-100 dark:bg-red-900/80 text-red-700 dark:text-red-300 px-1 py-0.2 rounded font-bold">You\'ll Get</span>' : '').'
            </div>
        </div>

        <!-- Metric 2: Credit Available / Limit -->
        <div class="flex flex-col min-w-0">
            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">Credit Balance</span>
            <div class="mt-0.5 flex items-baseline gap-1 flex-wrap">
                <span class="font-bold text-xs sm:text-sm text-emerald-600 dark:text-emerald-400">Rs.'.number_format($availableCredit, 2).'</span>
                <span class="text-[10px] text-gray-400 truncate">('.number_format($creditLimit, 0).' max)</span>
            </div>
        </div>

        <!-- Metric 3: Wallet Balance -->
        <div class="flex flex-col min-w-0">
            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">Main Wallet</span>
            <span class="font-bold text-xs text-gray-800 dark:text-gray-200 mt-0.5">Rs.'.number_format($mainBalance, 2).'</span>
        </div>

        <!-- Metric 4: Shopping Done -->
        <div class="flex flex-col min-w-0">
            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider truncate">Shop Done</span>
            <span class="font-bold text-xs text-blue-600 dark:text-blue-400 mt-0.5">Rs.'.number_format($totalShopping, 2).'</span>
        </div>
    </div>

    <!-- Action Bar: Direct Khata + Details Accordion -->
    <div class="flex items-center justify-between gap-1.5 pt-0.5">
        <a href="'.route('admin.customer-lookup.index', ['user_id' => $u->id]).'" class="flex-1 py-1.5 px-2.5 rounded-lg bg-[#1555a8] hover:bg-[#12468d] text-white text-xs font-bold text-center flex items-center justify-center gap-1.5 shadow-2xs transition-colors">
            <span>📖 Open Khata</span>
        </a>
        <button type="button" class="js-toggle-card-drawer py-1.5 px-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold flex items-center gap-1 transition-colors">
            <span>Details</span>
            <svg class="w-3.5 h-3.5 transform transition-transform duration-200 card-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
    </div>

    <!-- Collapsible Drawer Inside Card -->
    <div class="card-child-drawer hidden pt-2 space-y-2 border-t border-gray-100 dark:border-gray-700/60">
        '.$childDrawerHtml.'
    </div>
</div>';

            return [
                $u->id,
                $avatarImg,
                $customerNameCell,
                '<a href="mailto:'.e($u->email).'" class="text-blue-600 truncate inline-block max-w-[150px]">'.e($u->email).'</a>',
                $khataBadge,
                $mainBalanceFormatted,
                $shopDoneFormatted,
                'Rs.' . number_format($creditLimit, 2),
                $usedCreditFormatted,
                $availableCreditFormatted,
                $creditStatusBadge,
                $u->created_at?->format('Y-m-d'),
                $quickActions,
                $childDrawerHtml,
                $mobileCardHtml
            ];
        })->toArray();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();
        return view('admin.users_edit', compact('user'));
    }

    public function show(User $user)
    {
        $this->ensureAdmin();
        $user->load(['wallet', 'creditAccount', 'referralRecord']);
        
        $transactions = [];
        if ($user->wallet) {
            $transactions = \App\Models\WalletTransaction::where('wallet_id', $user->wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'tx_page');
        }

        $emis = \App\Models\EmiSchedule::where('user_id', $user->id)
            ->with('order')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('admin.users_show', compact('user', 'transactions', 'emis'));
    }

    public function genealogy(User $user)
    {
        $this->ensureAdmin();
        $user->load(['referralRecord'])->loadCount('referredUsers');
        
        return view('admin.genealogy.users_genealogy', compact('user'));
    }

    public function getGenealogyChildren(User $user)
    {
        $this->ensureAdmin();
        $children = $user->referredUsers()->with(['referralRecord'])->withCount('referredUsers')->get();
        
        if ($children->isEmpty()) {
            return '';
        }

        $html = '<ul>';
        foreach ($children as $child) {
            $html .= view('admin.partials.genealogy_node', [
                'user' => $child, 
                'isRoot' => false,
                'depth' => 1 // Reset depth for children being loaded
            ])->render();
        }
        $html .= '</ul>';

        return response($html);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|in:user,admin',
            'status' => 'required|string|in:active,pending,blocked',
            'password' => 'nullable|string|min:6',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_approval' => 'nullable|string|in:pending,approved,rejected',
            'kyc_status' => 'nullable|string|in:unfilled,pending,approved,rejected',
            'kyc_notes' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $oldStatus = $user->status;
        $oldKycStatus = $user->kyc_status;

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (array_key_exists('phone', $data)) {
            $user->phone = !empty($data['phone']) ? \App\Helpers\PhoneHelper::normalize($data['phone']) : null;
        }

        $user->role = $data['role'];
        $user->status = $data['status'];

        if (array_key_exists('kyc_status', $data)) {
            $user->kyc_status = $data['kyc_status'];
        }
        if (array_key_exists('kyc_notes', $data)) {
            $user->kyc_notes = $data['kyc_notes'];
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $ext = $file->getClientOriginalExtension();
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
            $file->move(public_path('images/user'), $filename);
            $user->avatar = 'images/user/' . $filename;
        }

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        // Manage Credit Account
        if (isset($data['credit_limit']) || isset($data['credit_approval'])) {
            $ca = \App\Models\CreditAccount::firstOrNew(['user_id' => $user->id]);
            $oldCreditStatus = $ca->exists ? $ca->approval_status : 'pending';
            
            if (isset($data['credit_limit'])) {
                $ca->credit_limit = $data['credit_limit'];
                // Recalculate available credit if limit changed
                $ca->available_credit = max(0, $ca->credit_limit - $ca->used_credit);
            }
            if (isset($data['credit_approval'])) {
                $ca->approval_status = $data['credit_approval'];
            }
            $ca->save();

            // Trigger commission if credit is approved for the first time AND status is active
            if ($oldCreditStatus !== 'approved' && $ca->approval_status === 'approved' && $user->status === 'active') {
                $this->mlmService->distributeJoiningCommissions($user->id);
            }
        }

        // Trigger commission if status changed to active AND credit is already approved
        if ($oldStatus === 'pending' && $user->status === 'active') {
            $ca = \App\Models\CreditAccount::where('user_id', $user->id)->first();
            if ($ca && $ca->approval_status === 'approved') {
                $this->mlmService->distributeJoiningCommissions($user->id);
            }
        }

        // Notify user of KYC change (Optional, but good practice)
        if ($oldKycStatus !== $user->kyc_status) {
            // Notification logic here if needed
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureAdmin();

        // prevent deleting self
        if (Auth::id() === $user->id) {
            return response()->json(['error' => 'cannot delete self'], 400);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }

    public function create()
    {
        $this->ensureAdmin();
        return view('admin.users_create');
    }

    /**
     * Create a user (admin-only endpoint).
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:user,admin',
            'status' => 'required|string|in:active,pending,blocked',
            'parent_id' => 'nullable|exists:users,id',
        ]);

        $requestedRole = $data['role'];

        // Only a super-admin (configured by env SUPER_ADMIN_EMAIL) may create admin users
        $superAdminEmail = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        if ($requestedRole === 'admin' && Auth::user()->email !== $superAdminEmail) {
            return back()->with('error', 'Only super-admin may create admin users')->withInput();
        }

        $u = new User();
        $u->name = $data['name'];
        $u->email = $data['email'];
        $u->password = bcrypt($data['password']);
        $u->role = $requestedRole;
        $u->status = $data['status'];
        $u->save();

        // Handle referral linkage
        $parentId = $data['parent_id'] ?? null;
        $parentLevel = 0;

        if ($parentId) {
            $parentRef = \App\Models\Referral::where('user_id', $parentId)->first();
            if ($parentRef) {
                $parentLevel = (int) $parentRef->level_depth;
            }
        }

        // generate a referral code for the new user
        $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
        while (\Illuminate\Support\Facades\DB::table('referrals')->where('referral_code', $newCode)->exists()) {
            $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
        }

        \Illuminate\Support\Facades\DB::table('referrals')->insert([
            'user_id' => $u->id,
            'parent_id' => $parentId,
            'referral_code' => $newCode,
            'level_depth' => $parentId ? ($parentLevel + 1) : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // NEW: If created as 'active', distribute joining commissions
        if ($u->status === 'active') {
            $this->mlmService->distributeJoiningCommissions($u->id);
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    public function searchUsers(Request $request)
    {
        $this->ensureAdmin();

        $term = $request->query('q');
        $users = User::search($term)
            ->where('status', 'active')
            ->with(['wallet', 'creditAccount', 'referralRecord'])
            ->orderBy('name')
            ->take(10)
            ->get();

        $data = $users->map(function($u) {
            $lastOrder = \App\Models\Order::where('user_id', $u->id)->orderBy('created_at', 'desc')->first();
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'referral_code' => $u->referralRecord->referral_code ?? 'N/A',
                'wallet_balance' => $u->wallet ? (float)$u->wallet->main_balance : 0,
                'credit_limit' => $u->creditAccount ? (float)$u->creditAccount->available_credit : 0,
                'credit_approved' => $u->creditAccount ? ($u->creditAccount->approval_status === 'approved') : false,
                'join_date' => $u->created_at ? $u->created_at->format('Y-m-d') : '-',
                'last_shopping' => $lastOrder ? $lastOrder->created_at->format('Y-m-d') : 'Never',
            ];
        });

        return response()->json($data);
    }
}
