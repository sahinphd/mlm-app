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

    public function index(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('admin.users');
    }

    public function genealogyIndex(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $userId = $request->query('user_id');
        
        if ($userId) {
            $user = User::with('referralRecord')->find($userId);
        } else {
            // Default to the first user (the root of the system)
            $user = User::with('referralRecord')->orderBy('id', 'asc')->first();
        }

        return view('admin.genealogy.genealogy', compact('user'));
    }

    // Server-side DataTables JSON
    public function data(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $columns = [
            0 => 'id',
            2 => 'name',
            3 => 'email',
            4 => 'role',
            5 => 'status',
            8 => 'created_at'
        ];

        $query = User::with('creditAccount');

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

        $rows = $data->map(function($u){
            $statusBadge = match($u->status) {
                'active' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Active</span>',
                'pending' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>',
                'blocked' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Blocked</span>',
                default => e($u->status)
            };

            $cs = $u->creditAccount->approval_status ?? 'N/A';
            $creditStatusBadge = match($cs) {
                'approved' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-success-50 text-success-600">Approved</span>',
                'pending' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-50 text-warning-600">Pending</span>',
                'rejected' => '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-50 text-danger-600">Rejected</span>',
                default => '<span class="text-xs text-gray-400">N/A</span>'
            };

            return [
                $u->id,
                '<img src="'.e($u->avatar_url).'" alt="avatar" class="w-8 h-8 rounded-full" />',
                e($u->name),
                '<a href="mailto:'.e($u->email).'" class="text-blue-600 truncate inline-block max-w-[150px]">'.e($u->email).'</a>',
                e($u->role ?? 'user'),
                $statusBadge,
                'Rs.' . number_format($u->creditAccount->credit_limit ?? 0, 2),
                $creditStatusBadge,
                $u->created_at?->format('Y-m-d'),
                '<a href="'.route('admin.users.show', $u->id).'" class="text-sm text-primary mr-2">View</a>' .
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
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.users_edit', compact('user'));
    }

    public function show(User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $user->load(['wallet', 'creditAccount', 'referralRecord']);
        
        $transactions = [];
        if ($user->wallet) {
            $transactions = \App\Models\WalletTransaction::where('wallet_id', $user->wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('admin.users_show', compact('user', 'transactions'));
    }

    public function genealogy(User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $user->load(['referralRecord']);
        
        // We'll build a tree structure. 
        // For simplicity in the view, we can just pass the user and use a recursive partial or 
        // a frontend library. Let's use a recursive approach in the view.
        
        return view('admin.genealogy.users_genealogy', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string|in:user,admin',
            'status' => 'required|string|in:active,pending,blocked',
            'password' => 'nullable|string|min:6',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_approval' => 'nullable|string|in:pending,approved,rejected',
            'avatar' => 'nullable|image|max:10',
        ]);

        $oldStatus = $user->status;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->role = $data['role'];
        $user->status = $data['status'];

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

            // Trigger commission if credit is approved for the first time
            if ($oldCreditStatus !== 'approved' && $ca->approval_status === 'approved') {
                $this->mlmService->distributeJoiningCommissions($user->id);
            }
        }

        if ($oldStatus === 'pending' && $user->status === 'active') {
            $this->mlmService->distributeJoiningCommissions($user->id);
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function destroy(Request $request, User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        // prevent deleting self
        if (Auth::id() === $user->id) {
            return response()->json(['error' => 'cannot delete self'], 400);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }

    public function create()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.users_create');
    }

    /**
     * Create a user (admin-only endpoint).
     */
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:user,admin',
            'status' => 'required|string|in:active,pending,blocked',
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

        // create a simple referral code entry (minimally)
        try {
            $code = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            while (\Illuminate\Support\Facades\DB::table('referrals')->where('referral_code', $code)->exists()) {
                $code = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            }
            \Illuminate\Support\Facades\DB::table('referrals')->insert([
                'user_id' => $u->id,
                'parent_id' => null,
                'referral_code' => $code,
                'level_depth' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // non-fatal
        }

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    public function searchUsers(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $term = $request->query('q');
        $users = User::search($term)
            ->where('status', 'active')
            ->with(['wallet', 'creditAccount'])
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
