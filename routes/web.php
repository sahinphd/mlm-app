<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\Api\Admin\PaymentApprovalController;

Route::get('/', function (Request $request) {
    if (Auth::check()) {
        return Auth::user()->role === 'admin' ? redirect('/admin') : redirect('/dashboard');
    }
    return view('auth.index');
});

// public auth pages
Route::get('/register', function(){ return view('auth.register'); })->name('register.view');
Route::get('/login', function(){ return view('auth.login'); })->name('login.view');
// Route::get('/forgot-password', function(){ return view('auth.forgot-password'); })->name('password.request');

// Password reset (forgot password)
Route::get('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/login/truecaller', [\App\Http\Controllers\Auth\TruecallerController::class, 'login'])->name('login.truecaller');
Route::get('/login/select-account', [\App\Http\Controllers\Auth\LoginController::class, 'showSelectAccount'])->name('login.select_account');
Route::post('/login/select-account', [\App\Http\Controllers\Auth\LoginController::class, 'selectAccount'])->name('login.select_account.post');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Password reset selection routes
Route::get('/password/select-account', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showSelectAccount'])->name('password.select_account');
Route::post('/password/select-account', [\App\Http\Controllers\Auth\PasswordResetController::class, 'selectAccount'])->name('password.select_account.post');

// Contact endpoint removed — mailing reverted

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function(){ 
        $user = \App\Models\User::find(Auth::id());
        $referralRecord = $user->referralRecord;

        if (!$referralRecord) {
            $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            while (\App\Models\Referral::where('referral_code', $newCode)->exists()) {
                $newCode = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8));
            }
            $referralRecord = \App\Models\Referral::create([
                'user_id' => $user->id,
                'parent_id' => null,
                'referral_code' => $newCode,
                'level_depth' => 0,
            ]);
        }

        $upcomingEmis = \App\Models\EmiSchedule::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $personalBv = $user->orders()->where('status', 'completed')->sum('total_bv');
        $totalReferrals = $user->referredUsers()->count();
        $activeReferrals = $user->referredUsers()->where('status', 'active')->count();
        
        $recentTransactions = \App\Models\WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->orderBy('created_at', 'desc')->take(5)->get();

        $settings = app(\App\Services\MLMService::class)->getSettings();
        $bvRate = (float)($settings['bv_conversion_rate'] ?? 1.0);
        $lockDays = (int) ($settings['commission_lock_period_days'] ?? 30);
        $now = now();
        $thresholdDate = $now->copy()->subDays($lockDays);

        // Cash Components
        $cashEarned = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', '!=', 'reversed')
            ->where('type', '!=', 'bv')
            ->sum('amount');
        
        $cashWithdrawn = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'withdrawn')
            ->where('type', '!=', 'bv')
            ->sum('amount');

        $withdrawableCommissions = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->sum('amount');

        $lockedCommissions = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', '!=', 'bv')
            ->where('created_at', '>', $thresholdDate)
            ->sum('amount');

        // BV Components (in Cash Value for aggregated cards)
        $withdrawableBvPoints = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'bv')
            ->where('created_at', '<=', $thresholdDate)
            ->sum('amount');
        
        $lockedBvPoints = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('type', 'bv')
            ->where('created_at', '>', $thresholdDate)
            ->sum('amount');
        
        $bvCashWithdrawn = \App\Models\WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'bv_withdrawal')
          ->where('type', 'credit')
          ->sum('amount');

        // Aggregated Metrics for Dashboard Cards (Strictly Cash Commissions)
        $totalWithdrawn = $cashWithdrawn;
        $totalWithdrawable = $withdrawableCommissions;
        $totalLocked = $lockedCommissions;
        $totalEarned = $totalWithdrawn + $totalWithdrawable + $totalLocked;

        $totalTDS = \App\Models\WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'commission_withdrawal')
          ->where('type', 'debit')
          ->where('description', 'LIKE', '%TDS%')
          ->sum('amount');

        $totalServiceCharge = \App\Models\WalletTransaction::whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('source', 'commission_withdrawal')
          ->where('type', 'debit')
          ->where('description', 'LIKE', '%Service Charge%')
          ->sum('amount');

        $nextReleaseRecord = \App\Models\Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>', $thresholdDate)
            ->orderBy('created_at', 'asc')
            ->first();
        
        $nextRelease = $nextReleaseRecord ? $nextReleaseRecord->created_at->addDays($lockDays) : null;
        
        $bvCashValue = ($user->wallet?->earning_balance ?? 0) * $bvRate;

        return view('dashboard', [
            'page' => 'ecommerce', 
            'referralRecord' => $referralRecord,
            'upcomingEmis' => $upcomingEmis,
            'personalBv' => $personalBv,
            'totalReferrals' => $totalReferrals,
            'activeReferrals' => $activeReferrals,
            'recentTransactions' => $recentTransactions,
            'totalCommissions' => $totalEarned,
            'totalWithdrawn' => $totalWithdrawn,
            'withdrawableCommissions' => $totalWithdrawable,
            'totalTDS' => $totalTDS,
            'totalServiceCharge' => $totalServiceCharge,
            'lockedCommissions' => $totalLocked,
            'nextRelease' => $nextRelease,
            'bvCashValue' => $bvCashValue,
            'withdrawableBvPoints' => $withdrawableBvPoints,
            'lockedBvPoints' => $lockedBvPoints
        ]); 
    })->name('dashboard');

    Route::view('/payments', 'payments.index')->name('payments.index');
    Route::get('/referrals', [\App\Http\Controllers\ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/genealogy', [\App\Http\Controllers\ReferralController::class, 'genealogy'])->name('genealogy.index');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/id-card', [\App\Http\Controllers\ProfileController::class, 'idCard'])->name('profile.id-card');
    Route::get('/commissions', [\App\Http\Controllers\CommissionController::class, 'index'])->name('commissions.index');
    Route::get('/commissions/bv', [\App\Http\Controllers\CommissionController::class, 'bvIndex'])->name('commissions.bv');
    Route::get('/commissions/withdrawal', [\App\Http\Controllers\CommissionWithdrawalController::class, 'index'])->name('commissions.withdrawal');
    Route::post('/commissions/withdraw', [\App\Http\Controllers\CommissionWithdrawalController::class, 'withdrawCommission'])->name('commissions.withdraw');
    Route::post('/commissions/convert-bv', [\App\Http\Controllers\CommissionWithdrawalController::class, 'convertBv'])->name('commissions.convert-bv');
    Route::get('/commissions/data', [\App\Http\Controllers\CommissionController::class, 'data'])->name('commissions.data');
    
    Route::get('/wallet/history', [\App\Http\Controllers\WalletHistoryController::class, 'walletIndex'])->name('wallet.history');
    Route::get('/wallet/history/data', [\App\Http\Controllers\WalletHistoryController::class, 'walletData'])->name('wallet.history.data');
    Route::get('/wallet/transfer', [\App\Http\Controllers\WalletTransferController::class, 'index'])->name('wallet.transfer');
    Route::post('/wallet/transfer', [\App\Http\Controllers\WalletTransferController::class, 'transfer'])->name('wallet.transfer.post');
    Route::get('/wallet/user-search', [\App\Http\Controllers\WalletTransferController::class, 'search'])->name('wallet.user.search');
    Route::get('/credit/history', [\App\Http\Controllers\WalletHistoryController::class, 'creditIndex'])->name('credit.history');
    Route::get('/credit/history/data', [\App\Http\Controllers\WalletHistoryController::class, 'creditData'])->name('credit.history.data');

    Route::get('/credit/emis', [\App\Http\Controllers\EmiController::class, 'index'])->name('credit.emis');
    Route::get('/credit/emis/data', [\App\Http\Controllers\EmiController::class, 'emiData'])->name('credit.emis.data');
    Route::get('/credit/penalties/history', [\App\Http\Controllers\WalletHistoryController::class, 'penaltyIndex'])->name('credit.penalties.history');
    Route::get('/credit/penalties/history/data', [\App\Http\Controllers\WalletHistoryController::class, 'penaltyData'])->name('credit.penalties.history.data');
    Route::post('/credit/emis/{id}/pay', [\App\Http\Controllers\EmiController::class, 'payEmi'])->name('credit.emis.pay');
    Route::post('/credit/penalties/{id}/pay', [\App\Http\Controllers\EmiController::class, 'payPenalty'])->name('credit.penalties.pay');

    Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/checkout', [\App\Http\Controllers\ShopController::class, 'checkout'])->name('shop.checkout');
    Route::post('/shop/place-order', [\App\Http\Controllers\ShopController::class, 'placeOrder'])->name('shop.place-order');
    Route::get('/orders', [\App\Http\Controllers\ShopController::class, 'orderHistory'])->name('orders.index');

    // Internal "API" routes using session auth
    Route::prefix('api')->group(function() {
        Route::get('payment-requests', [PaymentRequestController::class, 'index']);
        Route::post('payment-requests', [PaymentRequestController::class, 'store']);
        Route::get('payment-requests/{id}', [PaymentRequestController::class, 'show']);
        
        Route::get('wallet', [\App\Http\Controllers\Api\WalletController::class, 'show']);
        Route::get('credit-account', [\App\Http\Controllers\Api\CreditController::class, 'show']);
        Route::post('credit-account/request', [\App\Http\Controllers\Api\CreditController::class, 'requestApproval']);
        
        Route::get('orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::post('orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
        
        Route::get('products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
        Route::get('products/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

        Route::get('referrals/verify/{code}', [\App\Http\Controllers\Api\ReferralController::class, 'verify']);
        Route::get('users/verify-email/{email}', function($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if ($user) {
                return response()->json([
                    'success' => true,
                    'name' => $user->name
                ]);
            }
            return response()->json(['success' => false], 404);
        });
        Route::get('referrals/me', [\App\Http\Controllers\Api\ReferralController::class, 'myReferral']);

        Route::post('update-fcm-token', function(Request $request) {
            $request->validate(['fcm_token' => 'required|string']);
            $user = \App\Models\User::find(Auth::id());
            $user->fcm_token = $request->fcm_token;
            $user->save();
            return response()->json(['success' => true]);
        });

        // Admin API routes
        Route::prefix('admin')->group(function() {
            Route::get('payment-requests', [PaymentApprovalController::class, 'index']);
            Route::post('payment-requests/{id}/approve', [PaymentApprovalController::class, 'approve']);
            Route::post('payment-requests/{id}/reject', [PaymentApprovalController::class, 'reject']);
        });
    });

    // Admin Section
    Route::middleware('can:admin-access')->group(function () {
        Route::get('/admin', function(){
            // gather dashboard metrics
            $usersCount = \App\Models\User::count();
            $ordersCount = \App\Models\Order::count();
            $walletTotal = \App\Models\Wallet::selectRaw('COALESCE(SUM(COALESCE(main_balance,0) + COALESCE(earning_balance,0) + COALESCE(credit_balance,0)),0) as total')->value('total');

            $labels = []; $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i)->startOfDay();
                $labels[] = $day->format('M d');
                $data[] = \App\Models\User::whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count();
            }

            $projects = \App\Models\Project::orderBy('id','asc')->take(12)->get()->map(function($p){
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

            $recentPayments = \App\Models\PaymentRequest::with('user')->orderBy('created_at', 'desc')->take(5)->get();

            return view('admin.dashboard', compact('usersCount','ordersCount','walletTotal','labels','data','projects', 'recentPayments'));
        })->name('admin.dashboard');

        // Admin Users
        Route::get('/admin/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users');
        Route::get('/admin/kyc', [\App\Http\Controllers\Admin\UserController::class, 'kycIndex'])->name('admin.kyc.index');
        Route::get('/admin/genealogy', [\App\Http\Controllers\Admin\UserController::class, 'genealogyIndex'])->name('admin.genealogy.genealogy');
        Route::get('/admin/users/search', [\App\Http\Controllers\Admin\UserController::class, 'searchUsers'])->name('admin.users.search');
        Route::get('/admin/users/data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('admin.users.data');
        Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users/store', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/genealogy', [\App\Http\Controllers\Admin\UserController::class, 'genealogy'])->name('admin.users.genealogy');
        Route::get('/admin/users/{user}/genealogy-children', [\App\Http\Controllers\Admin\UserController::class, 'getGenealogyChildren'])->name('admin.users.genealogy.children');
        Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
        Route::post('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

        // Admin Products
        Route::get('/admin/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products');
        Route::get('/admin/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/admin/products/store', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/admin/products/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
        Route::post('/admin/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/admin/products/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');

        // Admin Packages
        Route::get('/admin/packages', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('admin.packages');
        Route::get('/admin/packages/create', [\App\Http\Controllers\Admin\PackageController::class, 'create'])->name('admin.packages.create');
        Route::post('/admin/packages/store', [\App\Http\Controllers\Admin\PackageController::class, 'store'])->name('admin.packages.store');
        Route::get('/admin/packages/{package}/edit', [\App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('admin.packages.edit');
        Route::post('/admin/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('admin.packages.update');
        Route::delete('/admin/packages/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('admin.packages.destroy');

        // Admin Orders
        Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders');
        Route::get('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/admin/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');

        // Admin Shop (Shop for any user)
        Route::get('/admin/shop', [\App\Http\Controllers\Admin\AdminShopController::class, 'index'])->name('admin.shop.index');
        Route::get('/admin/shop/checkout', [\App\Http\Controllers\Admin\AdminShopController::class, 'checkout'])->name('admin.shop.checkout');
        Route::post('/admin/shop/place-order', [\App\Http\Controllers\Admin\AdminShopController::class, 'placeOrder'])->name('admin.shop.place-order');

        // Admin Payments
        Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'index'])->name('admin.payments');
        Route::post('/admin/payments/{paymentRequest}/approve', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'approve'])->name('admin.payments.approve');
        Route::post('/admin/payments/{paymentRequest}/reject', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'reject'])->name('admin.payments.reject');
        Route::post('/admin/payments/{paymentRequest}/reopen', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'reopen'])->name('admin.payments.reopen');
        Route::get('/admin/payments/export', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'export'])->name('admin.payments.export');

        // Admin Commissions & Settings
        Route::get('/admin/commissions', [\App\Http\Controllers\CommissionController::class, 'adminIndex'])->name('admin.commissions');
        Route::get('/admin/commissions/bv', [\App\Http\Controllers\CommissionController::class, 'bvAdminIndex'])->name('admin.commissions.bv');
        Route::get('/admin/commissions/export', [\App\Http\Controllers\CommissionController::class, 'adminExport'])->name('admin.commissions.export');
        Route::get('/admin/commissions/bv/export', [\App\Http\Controllers\CommissionController::class, 'bvAdminExport'])->name('admin.commissions.bv.export');

        Route::get('/admin/wallet/history', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'walletIndex'])->name('admin.wallet.history');
        Route::get('/admin/wallet/history/data', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'walletData'])->name('admin.wallet.history.data');
        Route::get('/admin/wallet/transfer', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'transferIndex'])->name('admin.wallet.transfer');
        Route::post('/admin/wallet/transfer', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'transferProcess'])->name('admin.wallet.transfer.post');
        Route::get('/admin/credit/history', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'creditIndex'])->name('admin.credit.history');
        Route::get('/admin/credit/history/data', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'creditData'])->name('admin.credit.history.data');

        Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');

        Route::get('/admin/emis', [\App\Http\Controllers\Admin\EmiManagementController::class, 'index'])->name('admin.emis.index');
        Route::get('/admin/penalties/history', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'penaltyIndex'])->name('admin.penalties.history');
        Route::get('/admin/penalties/history/data', [\App\Http\Controllers\Admin\AdminWalletHistoryController::class, 'penaltyData'])->name('admin.penalties.history.data');
        Route::post('/admin/emis/{id}/remind', [\App\Http\Controllers\Admin\EmiManagementController::class, 'sendReminder'])->name('admin.emis.remind');

        Route::get('/admin/help', [\App\Http\Controllers\Admin\HelpController::class, 'index'])->name('admin.help');

        Route::get('/admin/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('admin.reports.export');
    });

    // Notifications
    Route::get('/notifications', function(){
        $user = \App\Models\User::find(Auth::id());
        $notes = $user->notifications()->orderBy('created_at','desc')->paginate(20);
        return view('notifications.index', compact('notes'));
    })->name('notifications.index');
    Route::post('/notifications/{id}/read', function($id){
        $note = \App\Models\User::find(Auth::id())->notifications()->where('id',$id)->first();
        if ($note) $note->markAsRead();
        return back();
    })->name('notifications.read');
});
