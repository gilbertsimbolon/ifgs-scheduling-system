<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-compact" data-assets-path="{{ asset('sneat/assets') }}/">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Indo Fitness Gym Sport® - Official Portal</title>
    <meta name="description" content="Portal Resmi Penjadwalan & Layanan Member Indo Fitness Gym Sport Tondano" />

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo-ifgs.jpg') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />

    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f9;
            color: #566a7f;
        }

        .landing-hero {
            background: linear-gradient(135deg, #1f2238 0%, #2e3256 100%);
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .landing-hero::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: radial-gradient(circle at 80% 20%, rgba(105, 108, 255, 0.25) 0%, transparent 50%);
            pointer-events: none;
        }

        .navbar-brand img {
            width: 44px;
            height: 44px;
            object-fit: cover;
        }

        .package-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 0.75rem;
        }

        .package-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(67, 89, 113, 0.12) !important;
        }

        .package-popular {
            border: 2px solid #696cff !important;
            position: relative;
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background-color: #696cff;
            color: #fff;
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        i.bx {
            vertical-align: -0.125em;
        }
    </style>
</head>

<body>
    <!-- 1. TOP NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-2">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS Logo" class="rounded-circle shadow-sm" />
                <div>
                    <span class="fw-bold text-dark fs-5 d-block lh-1">Indo Fitness Gym Sport®</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Sistem Penjadwalan & Layanan Member</small>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLanding" aria-controls="navbarLanding" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-paket">Paket Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-jadwal">Jam Operasional</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-trainer">Trainer Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-kontak">Kontak & Lokasi</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary px-3">
                            <i class="bx bx-log-in me-1"></i> Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary px-3">
                            <i class="bx bx-user-plus me-1"></i> Daftar Akun
                        </a>
                    @else
                        <!-- Logged-in User Pill -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 py-1 px-3 border shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar avatar-xs">
                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold" style="font-size: 0.75rem;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="text-start">
                                    <span class="fw-semibold text-dark d-block lh-1 small">{{ auth()->user()->name }}</span>
                                    <small class="badge bg-label-primary px-1 py-0 font-monospace" style="font-size: 0.65rem;">
                                        {{ auth()->user()->getRoleNames()->first() ?? 'Member' }}
                                    </small>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <h6 class="dropdown-header">Akun Saya</h6>
                                </li>
                                @if (auth()->user()->hasAnyRole(['Admin/Manager', 'Kasir']))
                                    <li>
                                        <a class="dropdown-item fw-semibold text-primary" href="{{ route('dashboard') }}">
                                            <i class="bx bx-home-smile me-2"></i> Ke Dashboard Panel
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="bx bx-user me-2"></i> Pengaturan Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bx bx-log-out me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                        @if (auth()->user()->hasAnyRole(['Admin/Manager', 'Kasir']))
                            <a href="{{ route('dashboard') }}" class="btn btn-primary d-none d-md-inline-flex align-items-center">
                                <i class="bx bx-layout me-1"></i> Dashboard
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- FLASH MESSAGES -->
    <div class="container-xl mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-alarm-exclamation fs-4 me-2"></i>
                    <div>{{ session('warning') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bx bx-error-circle fs-4 me-2 mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">Terdapat kesalahan pada formulir:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- 2. HERO SECTION -->
    <section class="landing-hero py-5 position-relative">
        <div class="container-xl py-4 position-relative" style="z-index: 2;">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="badge bg-label-primary text-uppercase px-3 py-2 rounded-pill mb-3 font-monospace" style="background-color: rgba(105, 108, 255, 0.2) !important; color: #a3a6ff !important;">
                        <i class="bx bx-dumbbell me-1"></i> Official Gym Center &bull; Tondano
                    </div>
                    <h1 class="display-5 fw-bold text-white mb-3 lh-sm">
                        Indo Fitness Gym Sport®
                    </h1>
                    <p class="lead text-white-50 mb-4" style="max-width: 600px;">
                        Pusat kebugaran, pembentukan tubuh, dan kelas Aerobic & Zumba terlengkap di Tondano. Nikmati fasilitas modern, pendampingan pelatih berlisensi, serta kemudahan reservasi latihan terjadwal berbasis Algoritma Cerdas.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg shadow">
                                <i class="bx bx-user-plus me-1"></i> Daftar Member Baru
                            </a>
                            <a href="#section-paket" class="btn btn-outline-light btn-lg">
                                <i class="bx bx-package me-1"></i> Lihat Paket & Harga
                            </a>
                        @else
                            @if (auth()->user()->hasRole('Member'))
                                @if ($activeMembership)
                                    <button type="button" class="btn btn-primary btn-lg shadow" data-bs-toggle="modal" data-bs-target="#modalTambahReservasi">
                                        <i class="bx bx-calendar-plus me-1"></i> Reservasi Kunjungan
                                    </button>
                                    <button type="button" class="btn btn-light text-primary btn-lg shadow" data-bs-toggle="modal" data-bs-target="#modalQrCodeMember">
                                        <i class="bx bx-qr-scan me-1"></i> QR Absensi Saya
                                    </button>
                                @else
                                    <button type="button" class="btn btn-warning text-dark btn-lg shadow fw-bold" data-bs-toggle="modal" data-bs-target="#modalOrderMembership">
                                        <i class="bx bx-cart-add me-1"></i> Berlangganan Membership
                                    </button>
                                @endif
                            @elseif (auth()->user()->hasRole('Trainer'))
                                <a href="#section-trainer-portal" class="btn btn-primary btn-lg shadow">
                                    <i class="bx bx-calendar-star me-1"></i> Kelola Sesi Trainer Saya
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg shadow">
                                    <i class="bx bx-home-smile me-1"></i> Buka Panel Dashboard
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>

                <div class="col-lg-5 text-center">
                    <div class="card bg-dark bg-opacity-50 border border-secondary shadow-lg rounded-4 p-4 text-white text-start">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar avatar-lg bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="bx bx-time fs-2 text-white"></i>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-0">Jam Buka Operasional</h5>
                                <small class="text-white-50">Tondano, Minahasa</small>
                            </div>
                        </div>

                        <div class="vstack gap-2 small">
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-50">
                                <span><i class="bx bx-check text-success me-1"></i> Fitness (Senin - Sabtu)</span>
                                <strong class="text-warning font-monospace">08.00 - 20.00</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-50">
                                <span><i class="bx bx-check text-success me-1"></i> Aerobic / Zumba (Senin & Kamis)</span>
                                <strong class="text-warning font-monospace">19.00 - 21.00</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span><i class="bx bx-x text-danger me-1"></i> Minggu & Hari Libur</span>
                                <strong class="text-danger">Tutup</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. SPECIAL AUTHENTICATED PORTALS -->

    <!-- A. PORTAL MEMBER -->
    @if (auth()->check() && auth()->user()->hasRole('Member'))
        <section id="section-member-portal" class="py-4 bg-white border-bottom shadow-sm">
            <div class="container-xl">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-label-primary font-monospace mb-1">PORTAL MEMBER IFGS</span>
                        <h3 class="fw-bold text-dark mb-0">Halo, {{ auth()->user()->name }}! 👋</h3>
                        <p class="text-muted small mb-0">Kelola status membership, reservasi kunjungan, dan absensi Anda di sini.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($activeMembership)
                            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahReservasi">
                                <i class="bx bx-calendar-plus me-1"></i> Reservasi Kunjungan
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalOrderMembership">
                            <i class="bx bx-cart me-1"></i> {{ $activeMembership ? 'Perpanjang Membership' : 'Pesan Membership' }}
                        </button>
                        <button type="button" class="btn btn-light text-primary border" data-bs-toggle="modal" data-bs-target="#modalQrCodeMember">
                            <i class="bx bx-qr-scan me-1"></i> QR Absensi
                        </button>
                    </div>
                </div>

                <!-- Notifikasi Pending jika ada -->
                @if (!empty($pendingMembership))
                    <div class="alert alert-warning border border-warning shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-warning text-white rounded p-2 d-flex align-items-center justify-content-center">
                                <i class="bx bx-time-five fs-2"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Pesanan Paket {{ $pendingMembership->product?->name }} Sedang Diverifikasi</h6>
                                <p class="small text-muted mb-0">
                                    No. Invoice: <strong class="font-monospace text-dark">{{ $pendingMembership->transaction?->invoice_number }}</strong> &bull;
                                    Nominal: <strong class="text-success">{{ $pendingMembership->formatted_price }}</strong> &bull;
                                    Metode: <strong>{{ $pendingMembership->paymentMethod ? ucwords(str_replace('_', ' ', $pendingMembership->paymentMethod->name)) : 'Transfer' }}</strong>
                                </p>
                            </div>
                        </div>
                        @if ($pendingMembership->payment_proof_url)
                            <a href="{{ $pendingMembership->payment_proof_url }}" target="_blank" class="btn btn-sm btn-outline-warning">
                                <i class="bx bx-image me-1"></i> Bukti Transfer
                            </a>
                        @endif
                    </div>
                @endif

                <div class="row g-4 mb-4">
                    <!-- Status Kartu Keanggotaan -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-label-primary">Status Keanggotaan</span>
                                    @if ($activeMembership)
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif (!empty($pendingMembership))
                                        <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Aktif</span>
                                    @endif
                                </div>

                                <h4 class="fw-bold text-dark mb-1">
                                    {{ $activeMembership ? $activeMembership->product->name : 'Belum Ada Paket' }}
                                </h4>
                                <p class="text-muted small mb-3">
                                    Kode Member: <strong class="font-monospace text-primary">{{ $member->member_code ?? (auth()->user()->user_code ?? '-') }}</strong>
                                </p>

                                @if ($activeMembership)
                                    <div class="p-3 bg-light rounded-3 small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Masa Berlaku s/d:</span>
                                            <strong class="text-dark">{{ $activeMembership->end_date->format('d M Y') }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Sisa Durasi:</span>
                                            <span class="badge bg-label-success">{{ $activeMembership->days_remaining }} Hari Lagi</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-3 bg-light rounded-3 small text-center text-muted">
                                        Silakan pilih paket di bawah untuk mengaktifkan membership dan memesan jadwal latihan.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Digital QR Card Preview -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #2b2c49 0%, #1e1e38 100%);">
                            <div class="card-body text-white d-flex align-items-center justify-content-between p-4">
                                <div>
                                    <span class="badge bg-label-primary font-monospace mb-2 text-uppercase">Kartu Member Digital</span>
                                    <h5 class="fw-bold text-white mb-1">{{ auth()->user()->name }}</h5>
                                    <p class="text-white-50 small mb-3 font-monospace">{{ auth()->user()->qr_code }}</p>
                                    <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalQrCodeMember">
                                        <i class="bx bx-fullscreen me-1"></i> Buka QR Absensi
                                    </button>
                                </div>
                                <div class="bg-white p-2 rounded-3 shadow cursor-pointer" data-bs-toggle="modal" data-bs-target="#modalQrCodeMember" title="Klik untuk memperbesar">
                                    {!! auth()->user()->getQrCodeSvg(88) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Kunjungan -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <span class="badge bg-label-info mb-2">Aktivitas Latihan</span>
                                <h5 class="fw-bold text-dark mb-3">Jadwal & Riwayat</h5>
                                <div class="d-flex justify-content-around text-center py-2 bg-light rounded-3">
                                    <div>
                                        <h4 class="fw-bold text-primary mb-0">{{ $myUpcomingSchedules->count() }}</h4>
                                        <small class="text-muted">Terjadwal</small>
                                    </div>
                                    <div class="vr"></div>
                                    <div>
                                        <h4 class="fw-bold text-success mb-0">{{ $myRecentVisits->count() }}</h4>
                                        <small class="text-muted">Kunjungan Hadir</small>
                                    </div>
                                    <div class="vr"></div>
                                    <div>
                                        <h4 class="fw-bold text-heading mb-0">{{ $myReservations->count() }}</h4>
                                        <small class="text-muted">Reservasi</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Jadwal Kunjungan Saya -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bx bx-calendar-star me-2 text-primary"></i> Jadwal Kunjungan Saya
                        </h5>
                        @if ($activeMembership)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahReservasi">
                                <i class="bx bx-plus me-1"></i> Buat Reservasi
                            </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Jadwal</th>
                                    <th>Tanggal</th>
                                    <th>Sesi / Waktu</th>
                                    <th>Paket</th>
                                    <th>Status Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myUpcomingSchedules as $sch)
                                    <tr>
                                        <td><strong class="font-monospace text-primary">{{ $sch->schedule_code }}</strong></td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $sch->scheduled_date->format('d M Y') }}</span>
                                            <small class="text-muted d-block">{{ $sch->scheduled_date->translatedFormat('l') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">
                                                {{ $sch->timeSlot->name ?? '-' }} ({{ $sch->timeSlot->time_range ?? '-' }})
                                            </span>
                                        </td>
                                        <td>{{ $sch->reservation?->membership?->product?->name ?? 'Membership' }}</td>
                                        <td>
                                            @if ($sch->status === 'attended')
                                                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Check-in</span>
                                            @elseif ($sch->status === 'scheduled')
                                                <span class="badge bg-label-primary"><i class="bx bx-time-five me-1"></i> Terjadwal</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ ucfirst($sch->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Belum ada jadwal kunjungan mendatang. Silakan klik <strong>"Buat Reservasi"</strong> untuk menjadwalkan latihan Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- B. PORTAL TRAINER -->
    @if (auth()->check() && auth()->user()->hasRole('Trainer'))
        <section id="section-trainer-portal" class="py-4 bg-white border-bottom shadow-sm">
            <div class="container-xl">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom">
                    <div>
                        <span class="badge bg-label-primary font-monospace mb-1">PORTAL TRAINER IFGS</span>
                        <h3 class="fw-bold text-dark mb-0">Coach {{ auth()->user()->name }} 🏋️</h3>
                        <p class="text-muted small mb-0">Kelola permohonan sesi latihan personal dari member yang memesan Anda.</p>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-6">
                        <i class="bx bx-check-shield me-1"></i> Instruktur Terverifikasi
                    </span>
                </div>

                <!-- Trainer Metrics Row -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <small class="text-muted d-block">Total Sesi</small>
                                    <h4 class="fw-bold mb-0">{{ $trainerMetrics['total'] }}</h4>
                                </div>
                                <div class="avatar bg-label-primary rounded p-2">
                                    <i class="bx bx-calendar-star fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border border-warning shadow-sm bg-warning bg-opacity-10">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <small class="text-warning fw-bold d-block">Menunggu Validasi</small>
                                    <h4 class="fw-bold text-warning mb-0">{{ $trainerMetrics['pending'] }}</h4>
                                </div>
                                <div class="avatar bg-warning text-white rounded p-2">
                                    <i class="bx bx-time fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border border-primary shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <small class="text-primary fw-bold d-block">Disetujui (ACC)</small>
                                    <h4 class="fw-bold text-primary mb-0">{{ $trainerMetrics['approved'] }}</h4>
                                </div>
                                <div class="avatar bg-label-primary rounded p-2">
                                    <i class="bx bx-check fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border border-success shadow-sm">
                            <div class="card-body d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <small class="text-success fw-bold d-block">Selesai</small>
                                    <h4 class="fw-bold text-success mb-0">{{ $trainerMetrics['completed'] }}</h4>
                                </div>
                                <div class="avatar bg-label-success rounded p-2">
                                    <i class="bx bx-award fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sesi Trainer Saya Table -->
                <div class="card shadow-sm">
                    <div class="card-header border-bottom py-3">
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bx bx-list-check me-2 text-primary"></i> Daftar Sesi Latihan Member
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Booking</th>
                                    <th>Member & Kontak</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Fokus Latihan</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($trainerBookings as $b)
                                    <tr>
                                        <td><strong class="font-monospace text-primary">{{ $b->booking_code }}</strong></td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $b->member->user->name ?? '-' }}</div>
                                            <small class="text-muted d-block">{{ $b->member->phone ?? '-' }}</small>
                                            @if ($b->whatsapp_url)
                                                <a href="{{ $b->whatsapp_url }}" target="_blank" class="badge bg-label-success text-decoration-none mt-1">
                                                    <i class="bx bxl-whatsapp me-1"></i> WhatsApp Member
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $b->session_date->format('d M Y') }}</span>
                                            <small class="d-block text-muted">{{ $b->timeSlot->name ?? '-' }} ({{ $b->timeSlot->time_range ?? '-' }})</small>
                                        </td>
                                        <td>
                                            <span class="text-heading fw-medium">{{ $b->training_focus }}</span>
                                            @if ($b->notes)
                                                <small class="d-block text-muted text-truncate" style="max-width: 200px;" title="{{ $b->notes }}">
                                                    Catatan: {{ $b->notes }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($b->status === 'pending')
                                                <span class="badge bg-warning text-dark">Menunggu Validasi</span>
                                            @elseif ($b->status === 'approved')
                                                <span class="badge bg-primary">Disetujui</span>
                                            @elseif ($b->status === 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif ($b->status === 'rejected')
                                                <span class="badge bg-danger">Ditolak</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($b->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($b->status === 'pending')
                                                <div class="d-flex justify-content-center gap-1">
                                                    <form action="{{ route('trainer-bookings.approve', $b) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="redirect_to" value="{{ route('home') }}">
                                                        <button type="submit" class="btn btn-sm btn-success px-2" title="Setujui Sesi (ACC)">
                                                            <i class="bx bx-check me-1"></i> Setujui
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger px-2 btn-reject-modal"
                                                        data-bs-toggle="modal" data-bs-target="#modalRejectSession"
                                                        data-action="{{ route('trainer-bookings.reject', $b) }}"
                                                        data-code="{{ $b->booking_code }}"
                                                        data-member="{{ $b->member->user->name ?? 'Member' }}">
                                                        <i class="bx bx-x me-1"></i> Tolak
                                                    </button>
                                                </div>
                                            @elseif ($b->status === 'approved')
                                                <form action="{{ route('trainer-bookings.complete', $b) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="redirect_to" value="{{ route('home') }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="bx bx-check-double me-1"></i> Selesaikan Sesi
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Belum ada permohonan sesi latihan yang diajukan ke Anda saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- 4. PAKET LAYANAN SECTION -->
    <section id="section-paket" class="py-5">
        <div class="container-xl">
            <div class="text-center mb-5">
                <span class="badge bg-label-primary font-monospace px-3 py-2 rounded-pill mb-2">PILIHAN KEANGGOTAAN</span>
                <h2 class="fw-bold text-dark">Paket Layanan Gym & Kelas</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Pilih paket yang sesuai dengan tujuan kebugaran Anda. Nikmati akses penuh ke area fitness dan kelas aerobik zumba.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse ($activeProducts as $product)
                    @php
                        $isPopular = str_contains(strtolower($product->name), '1 bulan');
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border package-card {{ $isPopular ? 'package-popular' : '' }}">
                            @if ($isPopular)
                                <div class="popular-badge">Paling Populer</div>
                            @endif
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-label-primary text-uppercase font-monospace">
                                            {{ $product->duration_formatted }}
                                        </span>
                                        <span class="badge bg-label-secondary font-monospace text-uppercase" style="font-size: 0.7rem;">
                                            {{ $product->category ?? 'Fitness' }}
                                        </span>
                                    </div>

                                    <h4 class="fw-bold text-dark mb-1">{{ $product->name }}</h4>
                                    <p class="text-muted small mb-3">{{ $product->description ?: 'Akses latihan optimal dengan instruktur profesional.' }}</p>

                                    <div class="mb-4">
                                        <span class="display-6 fw-bold text-primary">{{ $product->formatted_price }}</span>
                                        <small class="text-muted">/ {{ $product->duration_formatted }}</small>
                                    </div>

                                    <ul class="list-unstyled mb-4 vstack gap-2 small">
                                        <li class="d-flex align-items-center text-heading">
                                            <i class="bx bx-check-circle text-success me-2 fs-5"></i> Akses Fasilitas Gym Resmi IFGS
                                        </li>
                                        <li class="d-flex align-items-center text-heading">
                                            <i class="bx bx-check-circle text-success me-2 fs-5"></i> Reservasi Kunjungan Terjadwal
                                        </li>
                                        <li class="d-flex align-items-center text-heading">
                                            <i class="bx bx-check-circle text-success me-2 fs-5"></i> Kartu Member Digital & Presensi QR
                                        </li>
                                        @if (str_contains(strtolower($product->name), 'aerobic') || str_contains(strtolower($product->name), 'zumba'))
                                            <li class="d-flex align-items-center text-heading">
                                                <i class="bx bx-check-circle text-success me-2 fs-5"></i> Mengikuti Kelas Aerobic & Zumba
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                <div>
                                    @guest
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 py-2">
                                            <i class="bx bx-log-in me-1"></i> Masuk untuk Memesan
                                        </a>
                                    @else
                                        @if (auth()->user()->hasRole('Member'))
                                            <button type="button" class="btn btn-primary w-100 py-2 btn-order-specific"
                                                data-bs-toggle="modal" data-bs-target="#modalOrderMembership"
                                                data-product-id="{{ $product->id }}">
                                                <i class="bx bx-cart me-1"></i> Pilih Paket Ini
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary w-100 py-2" disabled>
                                                Pemesanan Khusus Member
                                            </button>
                                        @endif
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted">
                        Belum ada paket layanan aktif yang tersedia saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 5. JAM OPERASIONAL SECTION -->
    <section id="section-jadwal" class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="text-center mb-5">
                <span class="badge bg-label-primary font-monospace px-3 py-2 rounded-pill mb-2">WAKTU LATIHAN</span>
                <h2 class="fw-bold text-dark">Jadwal Operasional Resmi IFGS</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Jadwal teratur memastikan kenyamanan seluruh member agar ruang gym tidak mengalami overkapasitas.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse ($operationalSlots as $slot)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-label-primary text-uppercase font-monospace">{{ $slot->category_label }}</span>
                                    <small class="text-muted font-monospace"><i class="bx bx-user me-1"></i> Maks: {{ $slot->capacity }} Orang</small>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $slot->name }}</h5>
                                <p class="text-muted small mb-3">{{ $slot->days }}</p>
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <span class="fs-4 fw-bold text-primary font-monospace">{{ $slot->time_range }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted">
                        Data jadwal operasional belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 6. TRAINER SECTION -->
    <section id="section-trainer" class="py-5">
        <div class="container-xl">
            <div class="text-center mb-5">
                <span class="badge bg-label-primary font-monospace px-3 py-2 rounded-pill mb-2">INSTRUKTUR RESMI</span>
                <h2 class="fw-bold text-dark">Pelatih Profesional Kami</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Didampingi instruktur bersertifikasi untuk memastikan setiap gerakan dan sesi latihan Anda berjalan aman dan efektif.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse ($activeTrainers as $tr)
                    <div class="col-md-6 col-lg-5">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body p-4 d-flex gap-3 align-items-start">
                                <div class="avatar avatar-xl flex-shrink-0">
                                    <span class="avatar-initial rounded-circle bg-primary text-white fw-bold fs-3">
                                        {{ strtoupper(substr($tr->user->name ?? 'T', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="badge bg-label-success mb-1 font-monospace">Bersertifikasi APKI</span>
                                    <h5 class="fw-bold text-dark mb-1">{{ $tr->user->name ?? '-' }}</h5>
                                    <p class="text-primary small fw-semibold mb-2">{{ $tr->specialization }}</p>
                                    <p class="text-muted small mb-0">{{ $tr->bio ?: 'Instruktur resmi Indo Fitness Gym Sport Tondano.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted">
                        Data instruktur belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 7. FOOTER SECTION -->
    <footer id="section-kontak" class="bg-dark text-white pt-5 pb-4">
        <div class="container-xl">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" class="rounded-circle" style="width: 42px; height: 42px;" />
                        <h5 class="text-white fw-bold mb-0">Indo Fitness Gym Sport®</h5>
                    </div>
                    <p class="text-white-50 small">
                        Pusat kebugaran dan penjadwalan latihan modern di Tondano, Minahasa, Sulawesi Utara. Berkomitmen membentuk gaya hidup sehat dan tubuh ideal masyarakat.
                    </p>
                </div>

                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-3">Alamat & Kontak</h6>
                    <ul class="list-unstyled text-white-50 small vstack gap-2">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bx bx-map-pin fs-5 text-warning flex-shrink-0"></i>
                            <span>Tondano, Kabupaten Minahasa, Sulawesi Utara, Indonesia</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bx bx-phone fs-5 text-success flex-shrink-0"></i>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bx bx-envelope fs-5 text-primary flex-shrink-0"></i>
                            <span>indofitnessgym@gmail.com</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-4">
                    <h6 class="text-white fw-bold mb-3">Tautan Cepat</h6>
                    <ul class="list-unstyled text-white-50 small vstack gap-2">
                        <li><a href="#section-paket" class="text-white-50 text-decoration-none">Paket Keanggotaan</a></li>
                        <li><a href="#section-jadwal" class="text-white-50 text-decoration-none">Jadwal Latihan</a></li>
                        <li><a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Masuk ke Akun</a></li>
                        <li><a href="{{ route('register') }}" class="text-white-50 text-decoration-none">Pendaftaran Member Baru</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-top border-secondary pt-3 text-center small text-white-50">
                &copy; {{ date('Y') }} Indo Fitness Gym Sport®. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- MODALS FOR MEMBER & TRAINER -->

    @if (auth()->check() && auth()->user()->hasRole('Member'))
        <!-- Modal Order Membership Mandiri -->
        <div class="modal fade" id="modalOrderMembership" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-cart-add me-1 text-primary"></i> Berlangganan / Perpanjang Membership
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('memberships.order') }}" method="POST" enctype="multipart/form-data" id="formOrderMembership">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Pilihan Paket Layanan -->
                                <div class="col-md-6">
                                    <label for="orderProductId" class="form-label required fw-semibold">
                                        Pilih Paket Layanan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('product_id') is-invalid @enderror" id="orderProductId" name="product_id" required>
                                        <option value="">-- Pilih Paket Gym --</option>
                                        @foreach ($activeProducts as $prod)
                                            <option value="{{ $prod->id }}"
                                                data-price="{{ (float) $prod->price }}"
                                                data-formatted-price="{{ $prod->formatted_price }}"
                                                data-duration="{{ $prod->duration_formatted }}"
                                                {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                                {{ $prod->name }} &bull; {{ $prod->duration_formatted }} ({{ $prod->formatted_price }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tanggal Mulai -->
                                <div class="col-md-6">
                                    <label for="orderStartDate" class="form-label required fw-semibold">
                                        Tanggal Mulai Latihan <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="orderStartDate" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                                </div>

                                <!-- Metode Pembayaran -->
                                <div class="col-md-12">
                                    <label for="orderPaymentMethodId" class="form-label required fw-semibold">
                                        Metode Pembayaran Transfer / QRIS <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('payment_method_id') is-invalid @enderror" id="orderPaymentMethodId" name="payment_method_id" required>
                                        <option value="">-- Pilih Rekening Pembayaran --</option>
                                        @foreach ($activePaymentMethods as $pm)
                                            <option value="{{ $pm->id }}"
                                                data-type="{{ $pm->type }}"
                                                data-account-name="{{ $pm->account_name }}"
                                                data-account-number="{{ $pm->account_number }}"
                                                data-qr-image="{{ $pm->qr_image ? asset('storage/' . $pm->qr_image) : '' }}"
                                                {{ old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $pm->name)) }}
                                                @if ($pm->account_number)
                                                    ({{ $pm->account_number }} a/n {{ $pm->account_name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Petunjuk Pembayaran Dinamis -->
                                <div class="col-md-12">
                                    <div id="orderPaymentInstruction" class="card bg-lighter border border-primary p-3 d-none">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-primary"><i class="bx bx-info-circle me-1"></i> Petunjuk Transfer</span>
                                            <span class="badge bg-label-success fs-6" id="orderSummaryPrice">Rp 0</span>
                                        </div>
                                        <div id="orderBankDetails" class="small text-muted mb-2">
                                            Silakan transfer ke rekening: <strong id="orderAccountNumber" class="text-dark font-monospace fs-6"></strong>
                                            a/n <strong id="orderAccountName" class="text-dark"></strong>
                                        </div>
                                        <div id="orderQrDetails" class="text-center my-2 d-none">
                                            <img id="orderQrImg" src="" alt="QRIS IFGS" class="img-fluid rounded border shadow-sm p-1 bg-white" style="max-height: 180px;">
                                            <p class="small text-muted mt-1 mb-0">Scan kode QRIS di atas via m-Banking atau E-Wallet Anda.</p>
                                        </div>
                                        <small class="text-muted fst-italic">Pastikan nominal transfer tepat sesuai harga paket.</small>
                                    </div>
                                </div>

                                <!-- Upload Bukti Transfer -->
                                <div class="col-md-12">
                                    <label for="orderPaymentProof" class="form-label required fw-semibold">
                                        Unggah Foto / Bukti Transfer <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="orderPaymentProof" name="payment_proof" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                                    <div class="form-text small">Format JPG, PNG, atau WEBP. Maks 5MB.</div>

                                    <div id="orderProofPreviewContainer" class="mt-2 d-none text-center p-2 border rounded bg-light">
                                        <img id="orderProofPreviewImg" src="" alt="Preview Bukti Transfer" class="img-thumbnail" style="max-height: 160px;">
                                    </div>
                                </div>

                                <!-- Catatan Tambahan -->
                                <div class="col-md-12">
                                    <label for="orderNotes" class="form-label fw-semibold">Catatan Tambahan (Opsional)</label>
                                    <textarea class="form-control" id="orderNotes" name="notes" rows="2" placeholder="Nama rekening pengirim atau catatan lainnya...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="bx bx-send me-1"></i> Kirim Bukti Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Reservasi Kunjungan Baru -->
        <div class="modal fade" id="modalTambahReservasi" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bx bx-calendar-plus me-1 text-primary"></i> Reservasi Kunjungan Latihan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('reservations.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('home') }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="visitDateInput" class="form-label required fw-semibold">
                                    Tanggal Kunjungan <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="visitDateInput" name="visit_date"
                                    value="{{ old('visit_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="timeSlotSelect" class="form-label fw-semibold">
                                    Pilih Sesi Latihan (Opsional / Otomatis Greedy)
                                </label>
                                <select class="form-select" id="timeSlotSelect" name="time_slot_id">
                                    <option value="">-- Otomatis Pilih Slot Paling Lengang (Greedy) --</option>
                                    @foreach ($operationalSlots as $slot)
                                        <option value="{{ $slot->id }}">
                                            {{ $slot->name }} ({{ $slot->time_range }}) &bull; Sisa: {{ $slot->available_quota }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">
                                    Jika dikosongkan, Algoritma Greedy akan otomatis memilihkan slot dengan kepadatan paling optimal.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reservationNotes" class="form-label fw-semibold">Catatan (Opsional)</label>
                                <textarea class="form-control" id="reservationNotes" name="notes" rows="2" placeholder="Catatan latihan...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="bx bx-check me-1"></i> Konfirmasi Reservasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Perbesar QR Code Absensi -->
        <div class="modal fade" id="modalQrCodeMember" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content text-center shadow-lg border-0">
                    <div class="modal-header bg-primary text-white border-0 pb-3">
                        <div class="w-100 text-center">
                            <h5 class="modal-title text-white fw-bold d-inline-flex align-items-center justify-content-center">
                                <i class="bx bx-qr-scan me-2 fs-4"></i> QR Absensi Kunjungan
                            </h5>
                            <small class="text-white-50 d-block">Indo Fitness Gym Sport Tondano</small>
                        </div>
                        <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark mb-0">{{ auth()->user()->name }}</h5>
                            <p class="text-muted small mb-0">ID Member:
                                <code class="text-primary fw-bold fs-6">{{ $member->member_code ?? (auth()->user()->user_code ?? auth()->user()->qr_code) }}</code>
                            </p>
                        </div>

                        <div class="d-inline-block p-3 bg-white rounded-3 border border-2 border-dark shadow-sm my-2" style="max-width: 260px;">
                            {!! auth()->user()->getQrCodeSvg(230) !!}
                        </div>

                        <div class="mt-2">
                            <span class="badge bg-label-secondary font-monospace">{{ auth()->user()->qr_code }}</span>
                        </div>

                        <div class="alert alert-info py-2 px-3 mt-3 mb-0 text-start small">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-info-circle fs-5 me-2 flex-shrink-0"></i>
                                <div>
                                    Arahkan layar ponsel ini ke barcode scanner absensi di meja kasir saat memasuki area gym.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 pb-4 bg-white">
                        <a href="{{ route('user.qr-code.download') }}" class="btn btn-primary">
                            <i class="bx bx-download me-1"></i> Unduh File QR
                        </a>
                        <a href="{{ route('user.card', auth()->user()) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="bx bx-printer me-1"></i> Cetak Kartu Member
                        </a>
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (auth()->check() && auth()->user()->hasRole('Trainer'))
        <!-- Modal Tolak Sesi Latihan Trainer -->
        <div class="modal fade" id="modalRejectSession" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bx bx-x-circle me-1"></i> Tolak Permohonan Sesi Latihan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formRejectSession" action="" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="redirect_to" value="{{ route('home') }}">
                        <div class="modal-body">
                            <p class="small text-muted mb-3">
                                Anda akan menolak sesi latihan dari <strong id="rejectMemberName" class="text-dark"></strong> (<span id="rejectBookingCode" class="font-monospace"></span>).
                            </p>
                            <div class="mb-3">
                                <label for="rejectReason" class="form-label required fw-semibold">
                                    Alasan Penolakan <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="rejectReason" name="reason" rows="3" placeholder="Misal: Jadwal bertabrakan dengan agenda lain / Kuota sesi hari tersebut telah penuh..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bx bx-x me-1"></i> Konfirmasi Tolak Sesi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Core JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>

    <!-- Page Specific Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto select product in modal when clicking "Pilih Paket Ini"
            const selectOrderProduct = document.getElementById('orderProductId');
            const selectOrderPayment = document.getElementById('orderPaymentMethodId');
            const instructionBox = document.getElementById('orderPaymentInstruction');
            const priceSummary = document.getElementById('orderSummaryPrice');
            const bankDetails = document.getElementById('orderBankDetails');
            const accountNumberEl = document.getElementById('orderAccountNumber');
            const accountNameEl = document.getElementById('orderAccountName');
            const qrDetails = document.getElementById('orderQrDetails');
            const qrImg = document.getElementById('orderQrImg');
            const fileProofInput = document.getElementById('orderPaymentProof');
            const proofPreviewBox = document.getElementById('orderProofPreviewContainer');
            const proofPreviewImg = document.getElementById('orderProofPreviewImg');

            document.querySelectorAll('.btn-order-specific').forEach(btn => {
                btn.addEventListener('click', function() {
                    const prodId = this.getAttribute('data-product-id');
                    if (selectOrderProduct && prodId) {
                        selectOrderProduct.value = prodId;
                        updatePaymentInstruction();
                    }
                });
            });

            function updatePaymentInstruction() {
                if (!selectOrderProduct || !selectOrderPayment || !instructionBox) return;

                const selectedProd = selectOrderProduct.options[selectOrderProduct.selectedIndex];
                const selectedPm = selectOrderPayment.options[selectOrderPayment.selectedIndex];

                if (!selectedProd || !selectedProd.value || !selectedPm || !selectedPm.value) {
                    instructionBox.classList.add('d-none');
                    return;
                }

                instructionBox.classList.remove('d-none');
                if (priceSummary) priceSummary.textContent = selectedProd.getAttribute('data-formatted-price') || 'Rp 0';

                const accNumber = selectedPm.getAttribute('data-account-number') || '';
                const accName = selectedPm.getAttribute('data-account-name') || '';
                const qrImage = selectedPm.getAttribute('data-qr-image') || '';

                if (accNumber) {
                    if (accountNumberEl) accountNumberEl.textContent = accNumber;
                    if (accountNameEl) accountNameEl.textContent = accName;
                    if (bankDetails) bankDetails.classList.remove('d-none');
                } else {
                    if (bankDetails) bankDetails.classList.add('d-none');
                }

                if (qrImage) {
                    if (qrImg) qrImg.src = qrImage;
                    if (qrDetails) qrDetails.classList.remove('d-none');
                } else {
                    if (qrDetails) qrDetails.classList.add('d-none');
                }
            }

            if (selectOrderProduct) selectOrderProduct.addEventListener('change', updatePaymentInstruction);
            if (selectOrderPayment) selectOrderPayment.addEventListener('change', updatePaymentInstruction);

            if (fileProofInput) {
                fileProofInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file && proofPreviewBox && proofPreviewImg) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            proofPreviewImg.src = e.target.result;
                            proofPreviewBox.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    } else if (proofPreviewBox) {
                        proofPreviewBox.classList.add('d-none');
                    }
                });
            }

            // Trainer Reject Modal binding
            const rejectButtons = document.querySelectorAll('.btn-reject-modal');
            const formReject = document.getElementById('formRejectSession');
            const rejectMemberName = document.getElementById('rejectMemberName');
            const rejectBookingCode = document.getElementById('rejectBookingCode');

            rejectButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (formReject) {
                        formReject.action = this.getAttribute('data-action');
                        if (rejectMemberName) rejectMemberName.textContent = this.getAttribute('data-member');
                        if (rejectBookingCode) rejectBookingCode.textContent = this.getAttribute('data-code');
                    }
                });
            });
        });
    </script>
</body>

</html>
