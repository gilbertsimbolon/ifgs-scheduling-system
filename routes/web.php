<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pengguna (Admin/Manager bisa semua; Kasir hanya lihat)
    Route::middleware('role:Admin/Manager|Kasir')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
    });
    Route::middleware('role:Admin/Manager')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
        Route::put('/{user:slug}', [PenggunaController::class, 'update'])->name('update');
        Route::patch('/{user:slug}/status', [PenggunaController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user:slug}', [PenggunaController::class, 'destroy'])->name('destroy');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reservasi Kunjungan (Member & Admin)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/available-slots', [ReservationController::class, 'availableSlots'])->name('available-slots');
        Route::patch('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });

    // Jadwal Kunjungan (Member lihat jadwal sendiri, Admin lihat seluruh jadwal & kelola status)
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/optimize', [ScheduleController::class, 'optimize'])
            ->middleware('role:Admin/Manager')
            ->name('optimize');
        Route::patch('/{schedule}/status', [ScheduleController::class, 'updateStatus'])
            ->middleware('role:Admin/Manager|Kasir')
            ->name('update-status');
    });

    // Master Data Time Slot (Admin Only)
    Route::middleware('role:Admin/Manager')->prefix('time-slots')->name('time-slots.')->group(function () {
        Route::get('/', [TimeSlotController::class, 'index'])->name('index');
        Route::post('/', [TimeSlotController::class, 'store'])->name('store');
        Route::put('/{timeSlot}', [TimeSlotController::class, 'update'])->name('update');
        Route::patch('/{timeSlot}/status', [TimeSlotController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{timeSlot}', [TimeSlotController::class, 'destroy'])->name('destroy');
    });

    // Manajemen Member, Membership, Produk & Metode Pembayaran
    Route::middleware('role:Admin/Manager|Kasir')->group(function () {
        // Member
        Route::prefix('member')->name('member.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::put('/{member}', [MemberController::class, 'update'])->name('update');
            Route::patch('/{member}/status', [MemberController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
        });

        // Master Produk / Paket Layanan Gym
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

        // POS Kasir & Transaksi
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::post('/', [PosController::class, 'store'])->name('store');
            Route::get('/calculate', [PosController::class, 'calculate'])->name('calculate');
            Route::get('/receipt/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        });

        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        });
    });
});
