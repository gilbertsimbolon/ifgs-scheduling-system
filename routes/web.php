<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', function () {
        abort(404);
    })->name('password.reset');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('pengguna')->name('pengguna.')->group(function () {
    Route::get('/', [PenggunaController::class, 'index'])->name('index');
    Route::post('/', [PenggunaController::class, 'store'])->name('store');
    Route::put('/{user:slug}', [PenggunaController::class, 'update'])->name('update');
    Route::patch('/{user:slug}/status', [PenggunaController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{user:slug}', [PenggunaController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'role:Admin/Manager|Kasir'])->group(function () {
    // Member
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('index');
        Route::post('/', [MemberController::class, 'store'])->name('store');
        Route::put('/{member}', [MemberController::class, 'update'])->name('update');
        Route::patch('/{member}/status', [MemberController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
    });

    // Master Produk / Layanan Gym
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::patch('/{product}/status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Master Data Metode Pembayaran
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
        Route::patch('/{paymentMethod}/status', [PaymentMethodController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
    });

    // Transaksi Membership
    Route::prefix('memberships')->name('memberships.')->group(function () {
        Route::get('/', [MembershipController::class, 'index'])->name('index');
        Route::post('/', [MembershipController::class, 'store'])->name('store');
        Route::get('/calculate-end-date', [MembershipController::class, 'calculateEndDate'])->name('calculate-end-date');
        Route::put('/{membership}', [MembershipController::class, 'update'])->name('update');
        Route::patch('/{membership}/cancel', [MembershipController::class, 'cancel'])->name('cancel');
        Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('destroy');
    });
});
