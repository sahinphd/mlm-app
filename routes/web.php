<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function (Request $request) {
    if (Auth::check()) {
        return Auth::user()->isAdmin() ? redirect('/admin') : redirect('/payments');
    }
    return view('auth.index');
});

// public auth pages
Route::get('/register', function(){ return view('auth.register'); })->name('register.view');
Route::get('/login', function(){ return view('auth.login'); })->name('login.view');

// Password reset (forgot password)
Route::get('/password/forgot', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/payments', 'payments.index')->name('payments.index');
    Route::view('/admin/payments', 'payments.admin')->name('payments.admin');
    Route::get('/admin', function(){ if(!auth()->user()->isAdmin()) abort(403); return view('admin.dashboard'); })->name('admin.dashboard');
    Route::get('/dashboard', function(){ return view('dashboard'); })->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
