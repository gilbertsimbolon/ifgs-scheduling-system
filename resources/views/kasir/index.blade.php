@extends('layouts.app')

@section('title', 'Kasir / Front Desk')

@push('styles')
    <style>
        .kasir-touch-card {
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: 2px solid transparent;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .kasir-touch-card:hover {
            transform: translateY(-5px);
            border-color: var(--bs-primary);
            box-shadow: 0 0.75rem 1.5rem rgba(105, 108, 255, 0.15) !important;
        }

        .kasir-touch-card:active {
            transform: scale(0.98);
        }

        .kasir-icon-wrapper {
            width: 68px;
            height: 68px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 2.2rem;
            margin-bottom: 1.25rem;
            transition: transform 0.2s ease;
        }

        .kasir-touch-card:hover .kasir-icon-wrapper {
            transform: scale(1.08);
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header / Banner Kasir Tablet -->
        <div class="card bg-primary text-white mb-4 border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 position-relative">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-white text-primary fw-semibold px-2 py-1">
                                <i class="bx bx-store-alt me-1"></i> Mode Kasir / Front Desk
                            </span>
                            <span class="badge bg-label-secondary text-white border border-white-50 px-2 py-1">
                                <i class="bx bx-calendar me-1"></i> {{ $today }}
                            </span>
                        </div>
                        <h3 class="text-white fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }}!</h3>
                        <p class="text-white-50 mb-0">
                            Antarmuka operasional cepat untuk pendaftaran member, transaksi kasir, dan presensi gym.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white py-2 px-3 fs-6 d-inline-flex align-items-center">
                            <i class="bx bx-check-circle me-1"></i> Kasir Online
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Notifikasi Flash -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="bx bx-error fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Baris Kartu Aksi Kasir (3 Kartu per Baris untuk Tablet & Desktop) -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <!-- 1. KARTU TAMBAH PENGGUNA (Membuka Modal Instan) -->
            <div class="col">
                <div class="card h-100 kasir-touch-card shadow-sm border-0" data-bs-toggle="modal"
                    data-bs-target="#modalTambahPengguna" role="button" tabindex="0"
                    title="Klik untuk membuka formulir pendaftaran pengguna baru">
                    <div class="card-body p-4 d-flex flex-column justify-content-between text-center">
                        <div class="py-2">
                            <div class="kasir-icon-wrapper bg-label-primary text-primary mx-auto">
                                <i class="bx bx-user-plus"></i>
                            </div>
                            <h4 class="card-title fw-bold text-heading mb-2">Tambah Pengguna</h4>
                            <p class="card-text text-muted mb-4">
                                Daftarkan akun member atau pelanggan gym baru secara instan langsung di front desk.
                            </p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bx bx-plus-circle me-1"></i> Buka Formulir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. KARTU TRANSAKSI MEMBERSHIP -->
            <div class="col">
                <a href="{{ route('memberships.index') }}" class="text-decoration-none">
                    <div class="card h-100 kasir-touch-card shadow-sm border-0" role="button" tabindex="0"
                        title="Klik untuk membuka transaksi paket membership">
                        <div class="card-body p-4 d-flex flex-column justify-content-between text-center">
                            <div class="py-2">
                                <div class="kasir-icon-wrapper bg-label-success text-success mx-auto">
                                    <i class="bx bx-id-card"></i>
                                </div>
                                <h4 class="card-title fw-bold text-heading mb-2">Transaksi Membership</h4>
                                <p class="card-text text-muted mb-4">
                                    Pendaftaran baru atau perpanjangan paket membership gym dan pembayaran kasir.
                                </p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success w-100 py-2 fw-semibold">
                                    <i class="bx bx-credit-card me-1"></i> Kelola Transaksi
                                </button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- 3. KARTU PRESENSI & CEK-IN -->
            <div class="col">
                <a href="{{ route('schedules.index') }}" class="text-decoration-none">
                    <div class="card h-100 kasir-touch-card shadow-sm border-0" role="button" tabindex="0"
                        title="Klik untuk memproses presensi dan cek-in kedatangan member">
                        <div class="card-body p-4 d-flex flex-column justify-content-between text-center">
                            <div class="py-2">
                                <div class="kasir-icon-wrapper bg-label-info text-info mx-auto">
                                    <i class="bx bx-calendar-check"></i>
                                </div>
                                <h4 class="card-title fw-bold text-heading mb-2">Presensi & Cek-in</h4>
                                <p class="card-text text-muted mb-4">
                                    Verifikasi jadwal kunjungan, validasi kehadiran, dan konfirmasi cek-in member gym.
                                </p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-info text-white w-100 py-2 fw-semibold">
                                    <i class="bx bx-check-double me-1"></i> Cek Kehadiran
                                </button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna Terpadu -->
    @include('pengguna.modals.tambah')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Aksesibilitas keyboard untuk kartu Tambah Pengguna (Enter / Spasi)
            const tambahCard = document.querySelector('[data-bs-target="#modalTambahPengguna"]');
            if (tambahCard) {
                tambahCard.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const modalTambahEl = document.getElementById('modalTambahPengguna');
                        if (modalTambahEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalTambahEl);
                            modal.show();
                        }
                    }
                });
            }

            // Buka kembali modal secara otomatis jika terdapat validasi gagal
            @if ($errors->any() && old('_modal') === 'create')
                const modalTambahEl = document.getElementById('modalTambahPengguna');
                if (modalTambahEl && typeof bootstrap !== 'undefined') {
                    const modalTambah = new bootstrap.Modal(modalTambahEl);
                    modalTambah.show();
                }
            @endif
        });
    </script>
@endpush

