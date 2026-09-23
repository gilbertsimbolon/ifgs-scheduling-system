<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GreedyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipTransactionController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\TrainerBookingController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserQrCodeController;
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

// Landing Page Rute Utama (Terbuka untuk Umum / Tamu, Member, Trainer, Kasir, Admin)
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profil Pengguna (QR Code, Data Diri, Ganti Password)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // QR Code & Kartu Member Digital (Absensi Kunjungan)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/qr-code/download', [UserQrCodeController::class, 'download'])->name('qr-code.download');
        Route::get('/{user:slug}/qr-code/download', [UserQrCodeController::class, 'download'])->name('qr-code.download.user');
        Route::get('/{user:slug}/qr-code/svg', [UserQrCodeController::class, 'svg'])->name('qr-code.svg');
        Route::get('/{user:slug}/card', [UserQrCodeController::class, 'printCard'])->name('card');
    });

    // Pemesanan Paket Membership oleh Member Mandiri atau Staf
    Route::post('/memberships/order', [MembershipController::class, 'order'])->name('memberships.order');

    // Reservasi Kunjungan (Member, Kasir, Admin)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/available-slots', [ReservationController::class, 'availableSlots'])->name('available-slots');
        Route::patch('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });

    // Jadwal Kunjungan (Member lihat jadwal sendiri, Kasir & Admin kelola semua)
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/optimize', [ScheduleController::class, 'optimize'])
            ->middleware('role:Admin/Manager')
            ->name('optimize');
        Route::patch('/{schedule}/status', [ScheduleController::class, 'updateStatus'])
            ->middleware('role:Admin/Manager|Kasir')
            ->name('update-status');
    });

    // Sesi Latihan Personal Trainer (Member ajukan, Trainer/Admin/Kasir kelola)
    Route::prefix('trainer-bookings')->name('trainer-bookings.')->group(function () {
        Route::get('/', [TrainerBookingController::class, 'index'])->name('index');
        Route::post('/', [TrainerBookingController::class, 'store'])->name('store');
        Route::patch('/{trainerBooking}/approve', [TrainerBookingController::class, 'approve'])
            ->middleware('role:Admin/Manager|Kasir|Trainer')
            ->name('approve');
        Route::patch('/{trainerBooking}/reject', [TrainerBookingController::class, 'reject'])
            ->middleware('role:Admin/Manager|Kasir|Trainer')
            ->name('reject');
        Route::patch('/{trainerBooking}/complete', [TrainerBookingController::class, 'complete'])
            ->middleware('role:Admin/Manager|Kasir|Trainer')
            ->name('complete');
    });

    // Dashboard Gym (Diakses Staf Admin & Kasir; dialihkan ke home jika Member/Trainer)
    // Member Portal (Khusus Role Member - Mobile First Portal)
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [MemberPortalController::class, 'index'])->name('index');
        Route::get('/reservasi', [MemberPortalController::class, 'reservasi'])->name('reservasi');
        Route::get('/riwayat', [MemberPortalController::class, 'riwayat'])->name('riwayat');
        Route::get('/paket-layanan', [MemberPortalController::class, 'paketLayanan'])->name('paket-layanan');
        Route::get('/profil', [MemberPortalController::class, 'profil'])->name('profil');
        Route::put('/profil', [MemberPortalController::class, 'updateProfil'])->name('profil.update');
        Route::put('/profil/password', [MemberPortalController::class, 'updatePassword'])->name('profil.password');
    });

    // Dashboard Gym (Diakses Staf Admin & Kasir; dialihkan ke member portal jika Member)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Operasional Kasir & Admin (Membership, Presensi Check-in/out, Transaksi)
    Route::middleware('role:Admin/Manager|Kasir')->group(function () {
        // Presensi Check-in & Check-out Member via Barcode / QR Code
        Route::prefix('operasional')->name('attendances.')->group(function () {
            Route::get('/checkin-checkout', [AttendanceController::class, 'index'])->name('index');
            Route::post('/checkin-checkout/scan', [AttendanceController::class, 'scan'])->name('scan');
            Route::patch('/checkin-checkout/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('checkout');
            Route::delete('/checkin-checkout/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Member
        Route::prefix('member')->name('member.')->group(function () {
        // Manajemen Member (Admin / Kasir)
        Route::prefix('operasional/member')->name('admin-members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::put('/{member}', [MemberController::class, 'update'])->name('update');
            Route::patch('/{member}/status', [MemberController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
        });

        // Data Membership
        Route::prefix('memberships')->name('memberships.')->group(function () {
            Route::get('/', [MembershipController::class, 'index'])->name('index');
            Route::post('/', [MembershipController::class, 'store'])->name('store');
            Route::get('/calculate-end-date', [MembershipController::class, 'calculateEndDate'])->name('calculate-end-date');
            Route::put('/{membership}', [MembershipController::class, 'update'])->name('update');
            Route::patch('/{membership}/approve', [MembershipController::class, 'approve'])->name('approve');
            Route::patch('/{membership}/reject', [MembershipController::class, 'reject'])->name('reject');
            Route::patch('/{membership}/cancel', [MembershipController::class, 'cancel'])->name('cancel');
            Route::delete('/{membership}', [MembershipController::class, 'destroy'])->name('destroy');
        });

        // Validasi Transaksi Membership (Kasir / Admin ACC & Tolak Pembayaran)
        Route::prefix('transaksi-membership')->name('membership-transactions.')->group(function () {
            Route::get('/', [MembershipTransactionController::class, 'index'])->name('index');
            Route::patch('/{membership}/approve', [MembershipTransactionController::class, 'approve'])->name('approve');
            Route::patch('/{membership}/reject', [MembershipTransactionController::class, 'reject'])->name('reject');
        });

        // Riwayat Transaksi & Struk
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
            Route::get('/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('receipt');
        });
    });

    // Master Data & Konfigurasi Tingkat Lanjut (HANYA Admin/Manager)
    Route::middleware('role:Admin/Manager')->group(function () {
        // Pengguna (Admin Only)
        Route::prefix('pengguna')->name('pengguna.')->group(function () {
            Route::get('/', [PenggunaController::class, 'index'])->name('index');
            Route::post('/', [PenggunaController::class, 'store'])->name('store');
            Route::put('/{user:slug}', [PenggunaController::class, 'update'])->name('update');
            Route::patch('/{user:slug}/status', [PenggunaController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{user:slug}', [PenggunaController::class, 'destroy'])->name('destroy');
        });

        // Master Data Time Slot (Admin Only)
        Route::redirect('/time_slots', '/time-slots');
        Route::prefix('time-slots')->name('time-slots.')->group(function () {
            Route::get('/', [TimeSlotController::class, 'index'])->name('index');
            Route::post('/', [TimeSlotController::class, 'store'])->name('store');
            Route::put('/{timeSlot}', [TimeSlotController::class, 'update'])->name('update');
            Route::patch('/{timeSlot}/status', [TimeSlotController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{timeSlot}', [TimeSlotController::class, 'destroy'])->name('destroy');
        });

        // Master Produk / Paket Layanan Gym (Admin Only)
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::patch('/{product}/status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        // Master Trainer (Admin Only)
        Route::prefix('trainers')->name('trainers.')->group(function () {
            Route::get('/', [TrainerController::class, 'index'])->name('index');
            Route::post('/', [TrainerController::class, 'store'])->name('store');
            Route::put('/{trainer}', [TrainerController::class, 'update'])->name('update');
            Route::patch('/{trainer}/status', [TrainerController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{trainer}', [TrainerController::class, 'destroy'])->name('destroy');
        });

        // Master Data Metode Pembayaran (Admin Only)
        Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
            Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
            Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
            Route::patch('/{paymentMethod}/status', [PaymentMethodController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
        });

        // Operasional: Algoritma Greedy & Kuota Reservasi (Admin Only)
        Route::prefix('operasional/greedy')->name('greedy.')->group(function () {
            Route::get('/', [GreedyController::class, 'index'])->name('index');
            Route::patch('/slots/{timeSlot}/quota', [GreedyController::class, 'updateQuota'])->name('update-quota');
            Route::post('/optimize', [GreedyController::class, 'optimizeBatch'])->name('optimize');
        });
    });
});
