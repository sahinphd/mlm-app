<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\Api\Admin\PaymentApprovalController;

Route::get('/', function (Request $request) {
    if (Auth::check()) {
        return Auth::user()->isAdmin() ? redirect('/admin') : redirect('/dashboard');
    }
    return view('auth.index');
});

// public auth pages
Route::get('/register', function(){ return view('auth.register'); })->name('register.view');
Route::get('/login', function(){ return view('auth.login'); })->name('login.view');
Route::get('/forgot-password', function(){ return view('auth.forgot-password'); })->name('password.request');

// Password reset (forgot password)
Route::get('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function(){ 
        $user = auth()->user();
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

        return view('dashboard', ['page' => 'ecommerce', 'referralRecord' => $referralRecord]); 
    })->name('dashboard');

    Route::view('/payments', 'payments.index')->name('payments.index');
    Route::get('/referrals', [\App\Http\Controllers\ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/commissions', [\App\Http\Controllers\CommissionController::class, 'index'])->name('commissions.index');
    
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
        Route::get('referrals/me', [\App\Http\Controllers\Api\ReferralController::class, 'myReferral']);

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
        Route::get('/admin/genealogy', [\App\Http\Controllers\Admin\UserController::class, 'genealogyIndex'])->name('admin.genealogy.genealogy');
        Route::get('/admin/users/search', [\App\Http\Controllers\Admin\UserController::class, 'searchUsers'])->name('admin.users.search');
        Route::get('/admin/users/data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('admin.users.data');
        Route::get('/admin/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users/store', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::get('/admin/users/{user}/genealogy', [\App\Http\Controllers\Admin\UserController::class, 'genealogy'])->name('admin.users.genealogy');
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
        Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'index'])->name('payments.admin');
        Route::post('/admin/payments/{paymentRequest}/approve', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'approve'])->name('payments.admin.approve');
        Route::post('/admin/payments/{paymentRequest}/reject', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'reject'])->name('payments.admin.reject');
        Route::get('/admin/payments/export', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'export'])->name('payments.admin.export');

        // Admin Commissions & Settings
        Route::get('/admin/commissions', [\App\Http\Controllers\CommissionController::class, 'adminIndex'])->name('admin.commissions');
        Route::get('/admin/commissions/export', [\App\Http\Controllers\CommissionController::class, 'adminExport'])->name('admin.commissions.export');
        Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings');
        Route::post('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
        Route::get('/admin/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('admin.reports.export');
    });

    // Notifications
    Route::get('/notifications', function(){
        $notes = auth()->user()->notifications()->orderBy('created_at','desc')->paginate(20);
        return view('notifications.index', compact('notes'));
    })->name('notifications.index');
    Route::post('/notifications/{id}/read', function($id){
        $note = auth()->user()->notifications()->where('id',$id)->first();
        if ($note) $note->markAsRead();
        return back();
    })->name('notifications.read');
});
