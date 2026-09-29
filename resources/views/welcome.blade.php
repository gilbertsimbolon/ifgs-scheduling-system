<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-compact"
    data-assets-path="{{ asset('sneat/assets') }}/">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Indo Fitness Gym Sport® - Official Gym Center Tondano</title>
    <meta name="description" content="Pusat Kebugaran, Pembentukan Tubuh & Kelas Aerobic Zumba Resmi di Tondano" />

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo-ifgs.jpg') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}" />

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f9;
            color: #566a7f;
        }

        .landing-hero {
            background: linear-gradient(135deg, #1f2238 0%, #2e3256 100%);
            color: #ffffff;
            position: relative;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
        }

        .landing-hero::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: radial-gradient(circle at 80% 20%, rgba(105, 108, 255, 0.15) 0%, transparent 50%);
            pointer-events: none;
        }

        #section-paket,
        #section-faq,
        #section-trainer,
        #section-kontak {
            scroll-margin-top: 75px;
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

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLanding"
                aria-controls="navbarLanding" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-paket">Paket Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-trainer">Trainer Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold text-heading px-3" href="#section-kontak">Kontak & Lokasi</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary px-3">
                        <i class="bx bx-log-in me-1"></i> Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary px-3">
                        <i class="bx bx-user-plus me-1"></i> Daftar Akun
                    </a>
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
    </div>

    <!-- 2. HERO SECTION -->
    <section class="landing-hero py-5 position-relative">
        <div class="container-xl py-4 position-relative" style="z-index: 2;">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h1 class="display-5 fw-bold text-white mb-3 lh-sm"
                        style="font-family: Arial, Helvetica, sans-serif;">
                        Indo Fitness Gym Sport®
                    </h1>
                    <p class="lead mb-4"
                        style="max-width: 600px; color: #cbd5e1; font-family: Arial, Helvetica, sans-serif; font-size: 1.1rem; line-height: 1.6;">
                        Pusat kebugaran, pembentukan tubuh, dan kelas Aerobic & Zumba terlengkap di Tondano. Nikmati
                        fasilitas modern, pendampingan pelatih berlisensi, serta kemudahan reservasi latihan terjadwal
                        berbasis Algoritma Cerdas.
                    </p>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg shadow"
                            style="font-family: Arial, Helvetica, sans-serif;">
                            <i class="bx bx-user-plus me-1"></i> Daftar Member Baru
                        </a>
                        <a href="#section-paket" class="btn btn-outline-light btn-lg"
                            style="font-family: Arial, Helvetica, sans-serif;">
                            <i class="bx bx-package me-1"></i> Lihat Paket & Harga
                        </a>
                    </div>
                </div>

                <!-- Jam Buka Operasional (Dinamis dari Dashboard Admin) -->
                <div class="col-lg-5 text-center">
                    <div class="card shadow-lg rounded-4 p-4 text-white text-start"
                        style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); font-family: Arial, Helvetica, sans-serif;">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div
                                class="avatar avatar-lg bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center">
                                <i class="bx bx-time fs-2 text-white"></i>
                            </div>
                            <div>
                                <h5 class="text-white fw-bold mb-0">Jam Buka Operasional</h5>
                                <small class="text-white-50">Tondano, Minahasa</small>
                            </div>
                        </div>

                        <div class="vstack gap-2"
                            style="font-size: 0.92rem; font-family: Arial, Helvetica, sans-serif;">
                            @forelse ($operationalSlots as $slot)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                    style="border-color: rgba(255, 255, 255, 0.15) !important;">
                                    <span style="color: #e2e8f0;">
                                        <i class="bx bx-check text-success me-1"></i> {{ $slot->name }}
                                        ({{ $slot->days }})
                                    </span>
                                    <span class="fw-semibold text-white">
                                        {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}
                                        WITA
                                    </span>
                                </div>
                            @empty
                                <div class="py-2 text-white-50 small">Jadwal operasional belum diatur.</div>
                            @endforelse
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span style="color: #e2e8f0;"><i class="bx bx-x text-danger me-1"></i> Minggu & Hari
                                    Libur</span>
                                <span class="fw-semibold" style="color: #fca5a5;">Tutup</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. PAKET LAYANAN SECTION -->
    <section id="section-paket" class="py-5">
        <div class="container-xl">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark" style="font-family: Arial, Helvetica, sans-serif;">
                    Paket Layanan Gym & Kelas
                </h2>
                <p class="text-muted mx-auto" style="max-width: 600px; font-family: Arial, Helvetica, sans-serif;">
                    Pilih paket yang sesuai dengan tujuan kebugaran Anda. Nikmati akses penuh ke area fitness dan kelas
                    aerobik zumba.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse ($groupedServices as $service)
                    @php
                        $isPopular = $service->key === 'fitness';
                        $hasDurations = $service->durations && $service->durations->count() > 0;
                    @endphp
                    <div class="col-md-6 col-lg-5">
                        <div
                            class="card h-100 bg-white border shadow-sm package-card {{ $isPopular ? 'package-popular' : '' }}">
                            @if ($isPopular)
                                <span class="popular-badge">Paling Populer</span>
                            @endif
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Header Layanan -->
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="avatar avatar-md bg-light-danger text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 48px; height: 48px; background-color: #fee2e2;">
                                            <i class="bx {{ $service->icon }} fs-3 text-danger"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold text-dark mb-1">{{ $service->title }}</h4>
                                            <span class="badge bg-label-danger rounded-pill px-2 py-1"
                                                style="font-size: 0.72rem;">
                                                {{ $service->badge }}
                                            </span>
                                        </div>
                                    </div>

                                    <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.45;">
                                        {{ $service->description }}
                                    </p>

                                    @if ($hasDurations)
                                        <!-- Selector Durasi (Pills) -->
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="text-uppercase fw-bold text-muted"
                                                    style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                                    PILIH DURASI:
                                                </span>
                                                <span class="text-muted small" style="font-size: 0.7rem;">
                                                    {{ $service->durations->count() }} Pilihan Tarif
                                                </span>
                                            </div>

                                            <div class="d-flex flex-wrap gap-1 p-1 rounded-3"
                                                style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                                @foreach ($service->durations as $dur)
                                                    @php
                                                        $isActive = $dur->id === $service->default_duration->id;
                                                    @endphp
                                                    <button type="button"
                                                        class="btn btn-sm btn-service-pill flex-fill text-nowrap rounded-2 {{ $isActive ? 'btn-danger text-white shadow-sm' : 'btn-light text-dark bg-white border-0' }}"
                                                        style="font-size: 0.74rem; padding: 6px 10px; font-weight: 600;"
                                                        data-service-key="{{ $service->key }}"
                                                        data-price="{{ $dur->formatted_price }}"
                                                        data-duration="{{ $dur->duration_formatted }}">
                                                        {{ $dur->duration_formatted }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Box Harga Dinamis Sesuai Durasi -->
                                        <div class="p-3 rounded-3 mb-3"
                                            style="background-color: #fff5f5; border: 1px solid #fee2e2;">
                                            <div
                                                class="d-flex align-items-baseline justify-content-between flex-wrap gap-1">
                                                <div>
                                                    <span class="text-muted small d-block"
                                                        style="font-size: 0.68rem; font-weight: 600;">Tarif
                                                        Layanan:</span>
                                                    <span class="fs-2 fw-bold text-danger price-display-val"
                                                        id="price-display-{{ $service->key }}">
                                                        {{ $service->default_duration->formatted_price }}
                                                    </span>
                                                </div>
                                                <span
                                                    class="badge bg-white text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 duration-display-badge"
                                                    id="duration-display-{{ $service->key }}"
                                                    style="font-size: 0.72rem; font-weight: 600;">
                                                    / {{ $service->default_duration->duration_formatted }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Fasilitas & Keuntungan Layanan -->
                                    <div class="mb-4">
                                        <span class="text-uppercase fw-bold text-muted d-block small mb-2"
                                            style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                            Fasilitas Termasuk:
                                        </span>
                                        <ul class="list-unstyled mb-0 vstack gap-2 small">
                                            @foreach ($service->benefits as $benefit)
                                                <li class="d-flex align-items-center text-heading">
                                                    <i
                                                        class="bx bx-check-circle text-success me-2 fs-5 flex-shrink-0"></i>
                                                    <span>{{ $benefit }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <!-- Tombol Masuk untuk Membeli -->
                                <div>
                                    <a href="{{ route('login') }}"
                                        class="btn btn-outline-danger w-100 fw-semibold rounded-pill py-2">
                                        <i class="bx bx-log-in me-1"></i> Masuk untuk Membeli
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        Belum ada paket layanan aktif yang tersedia saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 4. FAQ SECTION (Menggantikan Jadwal Operasional) -->
    <section id="section-faq" class="py-5 bg-white border-top border-bottom">
        <div class="container-xl">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark" style="font-family: Arial, Helvetica, sans-serif;">
                    Pertanyaan Umum (FAQ)
                </h2>
                <p class="text-muted mx-auto" style="max-width: 600px; font-family: Arial, Helvetica, sans-serif;">
                    Informasi penting dan pertanyaan yang sering diajukan seputar keanggotaan dan latihan di Indo
                    Fitness Gym Sport®.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush shadow-sm border rounded-3 p-3 bg-white" id="accordionFaq">
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true"
                                    aria-controls="collapseOne">
                                    Bagaimana cara mendaftar menjadi member di Indo Fitness Gym Sport®?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                aria-labelledby="headingOne" data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted" style="line-height: 1.6;">
                                    Anda dapat mendaftar dengan membuat akun melalui tombol <strong>Daftar Akun</strong>
                                    pada halaman ini. Setelah akun aktif, masuk ke sistem untuk memilih paket layanan
                                    (Fitness atau Aerobic & Zumba) sesuai durasi yang diinginkan dan lakukan pembayaran.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false"
                                    aria-controls="collapseTwo">
                                    Kapan saja jam buka operasional gym?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted" style="line-height: 1.6;">
                                    Sesi latihan <strong>Fitness</strong> buka setiap hari <strong>Senin - Sabtu pukul
                                        08:00 - 20:00 WITA</strong>. Sedangkan kelas <strong>Aerobic & Zumba</strong>
                                    diselenggarakan setiap hari <strong>Senin & Kamis pukul 19:00 - 20:00 WITA</strong>.
                                    Hari Minggu dan libur nasional tutup.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false"
                                    aria-controls="collapseThree">
                                    Apakah tersedia paket harian (visit) tanpa langganan bulanan?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse"
                                aria-labelledby="headingThree" data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted" style="line-height: 1.6;">
                                    Ya, kami menyediakan pilihan paket <strong>1 Hari (Visit)</strong> bagi Anda yang
                                    ingin berolahraga secara harian atau mencoba fasilitas gym tanpa terikat paket
                                    bulanan.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false"
                                    aria-controls="collapseFour">
                                    Apakah member mendapatkan panduan dari pelatih (trainer)?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted" style="line-height: 1.6;">
                                    Tentu saja! Indo Fitness Gym Sport® memiliki instruktur dan pelatih resmi yang siap
                                    memandu gerakan dasar, penggunaan alat beban, serta instruktur kelas studio agar
                                    latihan Anda aman, nyaman, dan terarah.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false"
                                    aria-controls="collapseFive">
                                    Metode pembayaran apa saja yang diterima?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#accordionFaq">
                                <div class="accordion-body text-muted" style="line-height: 1.6;">
                                    Kami menerima pembayaran secara non-tunai melalui Transfer Bank dan QRIS instan,
                                    maupun pembayaran langsung secara tunai (cash) di meja kasir gym.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TRAINER SECTION (Layout: Foto Paling Atas di Tengah, Spesialisasi, Nama Pelatih) -->
    <section id="section-trainer" class="py-5">
        <div class="container-xl">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark" style="font-family: Arial, Helvetica, sans-serif;">
                    Pelatih Profesional Kami
                </h2>
                <p class="text-muted mx-auto" style="max-width: 600px; font-family: Arial, Helvetica, sans-serif;">
                    Didampingi instruktur terpercaya untuk memastikan setiap gerakan dan sesi latihan Anda berjalan aman
                    dan terarah.
                </p>
            </div>

            <div class="row g-4 justify-content-center">
                @forelse ($activeTrainers as $tr)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100 shadow-sm border text-center p-4">
                            <!-- 1. Profile picture paling atas di tengah -->
                            <div class="mx-auto mb-3">
                                @if ($tr->user?->avatar)
                                    <img src="{{ asset('storage/' . $tr->user->avatar) }}"
                                        alt="{{ $tr->user?->name }}"
                                        class="rounded-circle object-fit-cover shadow-sm"
                                        style="width: 100px; height: 100px;">
                                @else
                                    <div class="avatar rounded-circle bg-label-primary d-flex align-items-center justify-content-center mx-auto"
                                        style="width: 100px; height: 100px;">
                                        <span class="fs-2 fw-bold text-primary font-monospace">
                                            {{ strtoupper(substr($tr->user?->name ?? 'T', 0, 2)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- 2. Detail dia pelatih apa -->
                            <div class="mb-1">
                                <span class="text-primary fw-semibold small text-uppercase"
                                    style="letter-spacing: 0.5px;">
                                    {{ $tr->specialization ?? 'Fitness & Gym Trainer' }}
                                </span>
                            </div>

                            <!-- 3. Nama pelatih -->
                            <h5 class="fw-bold text-dark mb-0">
                                {{ $tr->user?->name ?? 'Trainer' }}
                            </h5>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bx bx-run fs-1 text-muted mb-2 d-block"></i>
                        Data pelatih belum tersedia saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 6. FOOTER SECTION -->
    <footer id="section-kontak" class="bg-dark text-white pt-5 pb-4">
        <div class="container-xl">
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" class="rounded-circle"
                            style="width: 42px; height: 42px;" />
                        <h5 class="text-white fw-bold mb-0">Indo Fitness Gym Sport®</h5>
                    </div>
                    <p class="text-white-50 small mb-0" style="max-width: 400px; line-height: 1.6;">
                        Pusat kebugaran, pembentukan tubuh, dan kelas studio Aerobic & Zumba resmi di Tondano, Minahasa,
                        Sulawesi Utara.
                    </p>
                </div>

                <div class="col-md-6 col-lg-7">
                    <h6 class="text-white fw-bold mb-3">Kontak & Lokasi</h6>
                    <ul class="list-unstyled text-white-50 small vstack gap-3 mb-0">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bx bx-map-pin fs-5 text-warning flex-shrink-0 mt-1"></i>
                            <span>
                                <strong>Alamat:</strong> 8W26+F7H, Wawalintouan, Kec. Tondano Bar., Kabupaten Minahasa,
                                Sulawesi Utara
                            </span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bx bx-phone fs-5 text-success flex-shrink-0"></i>
                            <span>
                                <strong>No HP:</strong> <a href="tel:0431321445"
                                    class="text-white text-decoration-none">0431-321445</a>
                            </span>
                        </li>
                        <li class="d-flex align-items-center gap-2 pt-1">
                            <a href="https://www.instagram.com/indofitnessgymsport/" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-pill px-3">
                                <i class="bx bxl-instagram me-1"></i> Instagram
                            </a>
                            <a href="https://www.facebook.com/IndoFitnessGymSport/" target="_blank"
                                class="btn btn-sm btn-outline-light rounded-pill px-3">
                                <i class="bx bxl-facebook me-1"></i> Facebook
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-top border-secondary pt-3 text-center small text-white-50">
                &copy; {{ date('Y') }} Indo Fitness Gym Sport®. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('sneat/assets/vendor/js/bootstrap.js') }}"></script>

    <!-- Page Specific Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handler interaktif tombol pilih durasi (Pill)
            document.querySelectorAll('.btn-service-pill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    var serviceKey = this.getAttribute('data-service-key');
                    var price = this.getAttribute('data-price');
                    var duration = this.getAttribute('data-duration');

                    // Update harga dan badge durasi
                    var priceEl = document.getElementById('price-display-' + serviceKey);
                    var durationEl = document.getElementById('duration-display-' + serviceKey);
                    if (priceEl && price) {
                        priceEl.textContent = price;
                    }
                    if (durationEl && duration) {
                        durationEl.textContent = '/ ' + duration;
                    }

                    // Update style tombol aktif dalam grup layanan yang sama
                    var groupPills = document.querySelectorAll(
                        '.btn-service-pill[data-service-key="' + serviceKey + '"]');
                    groupPills.forEach(function(p) {
                        p.className =
                            'btn btn-sm btn-service-pill flex-fill text-nowrap rounded-2 btn-light text-dark bg-white border-0';
                    });
                    this.className =
                        'btn btn-sm btn-service-pill flex-fill text-nowrap rounded-2 btn-danger text-white shadow-sm';
                });
            });
        });
    </script>
</body>

</html>
