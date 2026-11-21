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
        return view('admin.users');
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

        $columns = [
            0 => 'id',
            2 => 'name',
            3 => 'email',
            4 => 'role',
            5 => 'status',
            6 => 'kyc_status',
            9 => 'created_at'
        ];

        $query = User::with(['wallet', 'creditAccount', 'referralRecord.parent'])
            ->withSum(['emis as overdue_amount' => function($q) {
                $q->where('status', 'overdue');
            }], 'installment_amount')
            ->withSum(['commissions as total_commission' => function($q) {
                $q->where('type', '!=', 'bv');
            }], 'amount')
            ->withSum('bvCommissions as total_bv', 'amount');

        $recordsTotal = $query->count();

        // global search
        $search = $request->input('search.value');
        if ($search) {
            $query->search($search);
        }

        $recordsFiltered = $query->count();

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
                'active' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Active</span>',
                'pending' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>',
                'blocked' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Blocked</span>',
                default => e($u->status)
            };

            $kycBadge = match($u->kyc_status) {
                'approved' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Approved</span>',
                'pending' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>',
                'rejected' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Rejected</span>',
                default => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Unfilled</span>'
            };

            $cs = $u->creditAccount->approval_status ?? 'N/A';
            $creditStatusBadge = match($cs) {
                'approved' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Approved</span>',
                'pending' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>',
                'rejected' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Rejected</span>',
                default => '<span class="text-xs text-gray-400">N/A</span>'
            };

            $parentName = $u->referralRecord?->parent?->name ?? 'None';
            $referralCode = $u->referralRecord?->referral_code ?? '-';

            $userLimit = (float) ($u->creditAccount->credit_limit ?? $defaultLimit);

            $canQuickOnboard = ($u->status === 'pending' || $u->kyc_status === 'pending');
            $quickOnboardBtn = $canQuickOnboard ? 
                '<button type="button" class="js-quick-onboard-btn text-xs font-semibold bg-primary/10 text-primary hover:bg-primary hover:text-white px-2 py-1 rounded transition-colors mr-2 inline-flex items-center gap-1" data-id="'.$u->id.'" data-name="'.e($u->name).'" data-email="'.e($u->email).'" data-phone="'.e($u->phone ?? '-').'" data-aadhaar="'.e($u->aadhaar_number ?? 'N/A').'" data-pan="'.e($u->pan_number ?? 'N/A').'" data-address="'.e($u->address ?? 'N/A').'" data-nominee-name="'.e($u->nominee_name ?? 'N/A').'" data-nominee-rel="'.e($u->nominee_relation ?? 'N/A').'" data-kyc-status="'.e($u->kyc_status).'" data-status="'.e($u->status).'" data-credit-limit="'.$userLimit.'" data-upline="'.e($parentName).'" data-avatar="'.e($u->avatar_url).'" title="Quick Complete Approval">⚡ Onboard</button>' : '';

            return [
                $u->id,
                '<img src="'.e($u->avatar_url).'" 
                      alt="avatar" 
                      class="w-10 h-10 rounded-full border border-stroke dark:border-strokedark cursor-pointer js-user-info" 
                      data-id="'. $u->id .'"
                      data-name="'.e($u->name).'"
                      data-phone="'.e($u->phone ?? '-').'"
                      data-ref-by="'.e($parentName).'"
                      data-ref-code="'.e($referralCode).'"
                      data-avatar="'.e($u->avatar_url).'"
                      data-wallet="'.number_format($u->wallet->main_balance ?? 0, 2).'"
                      data-commission="'.number_format($u->total_commission ?? 0, 2).'"
                      data-bv="'.number_format($u->total_bv ?? 0, 2).'"
                      data-credit-used="'.number_format($u->creditAccount->used_credit ?? 0, 2).'"
                      data-overdue="'.number_format($u->overdue_amount ?? 0, 2).'"
                      data-kyc-status="'.e($u->kyc_status).'"
                      data-aadhaar="'.e($u->aadhaar_number ?? 'N/A').'"
                      data-pan="'.e($u->pan_number ?? 'N/A').'"
                      data-address="'.e($u->address ?? 'N/A').'"
                      data-nominee="'.e(($u->nominee_name ? $u->nominee_name . ' (' . ($u->nominee_relation ?? 'Nominee') . ')' : 'N/A')).'"
                      data-credit-limit="'.$userLimit.'"
                      data-can-onboard="'.($canQuickOnboard ? '1' : '0').'"
                />',
                e($u->name),
                '<a href="mailto:'.e($u->email).'" class="text-blue-600 truncate inline-block max-w-[150px]">'.e($u->email).'</a>',
                e($u->role ?? 'user'),
                $statusBadge,
                $kycBadge,
                'Rs.' . number_format($u->creditAccount->credit_limit ?? 0, 2),
                $creditStatusBadge,
                $u->created_at?->format('Y-m-d'),
                $quickOnboardBtn .
                '<a href="'.route('admin.users.show', $u->id).'" class="text-sm text-primary mr-2">View</a>' .
                ((!$u->isAdmin()) ? '<form action="'.route('admin.impersonate', $u->id).'?redirect=profile" method="POST" class="inline mr-2">'.csrf_field().'<button type="submit" class="text-sm text-emerald-600 font-semibold hover:underline" title="Login as User">Login</button></form>' : '') .
                '<a href="'.route('admin.users.genealogy', $u->id).'" class="text-sm text-success mr-2">Genealogy</a>' .
                '<a href="/admin/users/'. $u->id .'/edit" class="text-sm text-blue-600 mr-2">Edit</a>' .
                '<a href="#" data-id="'. $u->id .'" class="text-sm text-red-600 js-delete">Delete</a>'
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
