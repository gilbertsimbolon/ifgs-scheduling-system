<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Pengguna (Admin/Manager bisa kelola penuh; Kasir bisa lihat dan tambah pengguna)
    Route::middleware('role:Admin/Manager|Kasir')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
    });
    Route::middleware('role:Admin/Manager')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::put('/{user:slug}', [PenggunaController::class, 'update'])->name('update');
        Route::patch('/{user:slug}/status', [PenggunaController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user:slug}', [PenggunaController::class, 'destroy'])->name('destroy');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Reservasi Kunjungan (Member & Admin)
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/available-slots', [ReservationController::class, 'availableSlots'])->name('available-slots');
        Route::patch('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });

    // Pemesanan Paket Membership oleh Member (Upload Bukti Transfer)
    Route::post('/memberships/order', [MembershipController::class, 'order'])->name('memberships.order');

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

    // Sesi Latihan Personal Trainer (Validasi ACC / Tolak oleh Trainer atau Admin)
    Route::middleware('role:Admin/Manager|Trainer|Kasir|Member')->prefix('trainer-bookings')->name('trainer-bookings.')->group(function () {
        Route::get('/', [TrainerBookingController::class, 'index'])->name('index');
        Route::post('/', [TrainerBookingController::class, 'store'])->name('store');
        Route::patch('/{trainerBooking}/approve', [TrainerBookingController::class, 'approve'])->name('approve');
        Route::patch('/{trainerBooking}/reject', [TrainerBookingController::class, 'reject'])->name('reject');
        Route::patch('/{trainerBooking}/complete', [TrainerBookingController::class, 'complete'])->name('complete');
    });

    // Operasional: Presensi Check-in & Check-out Member via Barcode / QR Code
    Route::middleware('role:Admin/Manager|Kasir|Trainer')->prefix('operasional')->name('attendances.')->group(function () {
        Route::get('/checkin-checkout', [AttendanceController::class, 'index'])->name('index');
        Route::post('/checkin-checkout/scan', [AttendanceController::class, 'scan'])->name('scan');
        Route::patch('/checkin-checkout/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('checkout');
        Route::delete('/checkin-checkout/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
    });

    // Master Data Time Slot (Admin Only)
    Route::redirect('/time_slots', '/time-slots');
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

        // Manajemen Trainer
        Route::prefix('trainers')->name('trainers.')->group(function () {
            Route::get('/', [TrainerController::class, 'index'])->name('index');
            Route::post('/', [TrainerController::class, 'store'])->name('store')->middleware('role:Admin/Manager');
            Route::put('/{trainer}', [TrainerController::class, 'update'])->name('update')->middleware('role:Admin/Manager');
            Route::patch('/{trainer}/status', [TrainerController::class, 'toggleStatus'])->name('toggle-status')->middleware('role:Admin/Manager');
            Route::delete('/{trainer}', [TrainerController::class, 'destroy'])->name('destroy')->middleware('role:Admin/Manager');
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
});
