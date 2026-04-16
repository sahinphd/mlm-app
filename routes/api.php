<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\Api\Admin\PaymentApprovalController;

// NOTE: This API uses session auth by default for local dev. For production, install and configure Sanctum.

Route::middleware('auth')->group(function () {
    Route::get('payment-requests', [PaymentRequestController::class, 'index']);
    Route::post('payment-requests', [PaymentRequestController::class, 'store']);
    Route::get('payment-requests/{id}', [PaymentRequestController::class, 'show']);
    // Wallet and credit APIs
    Route::get('wallet', [\App\Http\Controllers\Api\WalletController::class, 'show']);
    Route::post('wallet/credit', [\App\Http\Controllers\Api\WalletController::class, 'credit']);
    Route::post('wallet/debit', [\App\Http\Controllers\Api\WalletController::class, 'debit']);

    Route::get('credit-account', [\App\Http\Controllers\Api\CreditController::class, 'show']);
    Route::post('credit-account/request', [\App\Http\Controllers\Api\CreditController::class, 'requestApproval']);
    // Orders
    Route::get('orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::post('orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
});

// Admin endpoints (checks role in controller)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('payment-requests', [PaymentApprovalController::class, 'index']);
    Route::post('payment-requests/{id}/approve', [PaymentApprovalController::class, 'approve']);
    Route::post('payment-requests/{id}/reject', [PaymentApprovalController::class, 'reject']);

    // Admin credit approval
    Route::get('credit-accounts', [\App\Http\Controllers\Api\Admin\CreditApprovalController::class, 'index']);
    Route::post('credit-accounts/{id}/approve', [\App\Http\Controllers\Api\Admin\CreditApprovalController::class, 'approve']);
    Route::post('credit-accounts/{id}/reject', [\App\Http\Controllers\Api\Admin\CreditApprovalController::class, 'reject']);
});
