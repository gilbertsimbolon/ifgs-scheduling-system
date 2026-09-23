@extends('layouts.member')

@section('title', 'Beranda')

@section('content')
    <!-- Header Salam Personal -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <span class="text-muted small d-block" style="font-size: 0.78rem;">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
            <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
                Hi, {{ $user->name }} 👋
            </h5>
        </div>
        <div>
            @if ($currentAttendance)
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
                    <i class="bx bx-check-circle me-1"></i> Sedang di Gym
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Notifikasi Status Presensi Hari Ini jika Ada -->
    @if ($currentAttendance)
        <div class="card border-0 bg-success bg-opacity-10 mb-3 shadow-none">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bx bx-run fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-success small">Presensi Aktif Hari Ini</div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            Check-in pukul {{ \Carbon\Carbon::parse($currentAttendance->check_in_at)->format('H:i') }} WITA
                        </small>
                    </div>
                </div>
                <a href="{{ route('member.riwayat') }}" class="btn btn-sm btn-outline-success">
                    Riwayat
                </a>
            </div>
        </div>
    @endif

    <!-- 1. MEMBERSHIP CARD (Digital Card) -->
    <div class="card border-0 text-white mb-3 shadow-sm overflow-hidden position-relative"
        style="background: linear-gradient(135deg, #1e1e2d 0%, #2b2b40 50%, #3a3b5c 100%); border-radius: 16px;">
        <!-- Background decorative shape -->
        <div class="position-absolute end-0 top-0 translate-middle-y opacity-10" style="margin-right: -20px; margin-top: 10px;">
            <i class="bx bx-dumbbell" style="font-size: 9rem; color: #ffffff;"></i>
        </div>

        <div class="card-body p-3 p-sm-4 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" width="34" height="34"
                        class="rounded-circle border border-white border-2" />
                    <div>
                        <span class="fw-bold text-white small d-block lh-1" style="letter-spacing: 0.5px;">INDO FITNESS GYM</span>
                        <small class="text-white-50" style="font-size: 0.68rem;">MEMBER CARD</small>
                    </div>
                </div>
                <div>
                    @if ($activeMembership)
                        <span class="badge bg-success text-white px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                            <i class="bx bx-check-circle me-1"></i> Aktif
                        </span>
                    @elseif ($pendingMembership)
                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                            <i class="bx bx-time me-1"></i> Menunggu Verifikasi
                        </span>
                    @else
                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                            Tidak Aktif
                        </span>
                    @endif
                </div>
            </div>

            @if ($activeMembership)
                <div class="mb-3">
                    <span class="text-white-50 small d-block" style="font-size: 0.72rem;">Paket Membership</span>
                    <h5 class="fw-bold text-white mb-0" style="letter-spacing: -0.2px;">
                        {{ $activeMembership->product?->name ?? 'Membership Reguler' }}
                    </h5>
                </div>

                <div class="row g-2 pt-2 border-top border-white border-opacity-10">
                    <div class="col-6">
                        <span class="text-white-50 d-block" style="font-size: 0.7rem;">Berlaku Sampai</span>
                        <strong class="text-white small">
                            {{ $activeMembership->end_date ? $activeMembership->end_date->translatedFormat('d M Y') : '-' }}
                        </strong>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-white-50 d-block" style="font-size: 0.7rem;">Sisa Durasi</span>
                        <strong class="text-warning small">
                            {{ $activeMembership->days_remaining }} hari
                        </strong>
                    </div>
                </div>
            @elseif ($pendingMembership)
                <div class="mb-2">
                    <span class="text-white-50 small d-block" style="font-size: 0.72rem;">Pengajuan Paket</span>
                    <h6 class="fw-bold text-white mb-1">
                        {{ $pendingMembership->product?->name ?? 'Paket Baru' }}
                    </h6>
                    <small class="text-white-50 d-block" style="font-size: 0.72rem;">
                        Total: {{ $pendingMembership->formatted_price }} (Sedang diverifikasi oleh staf)
                    </small>
                </div>
            @else
                <div class="py-2">
                    <h6 class="fw-bold text-white mb-1">Membership Tidak Aktif</h6>
                    <p class="text-white-50 small mb-2" style="font-size: 0.75rem;">
                        Aktifkan paket membership Anda untuk mulai reservasi dan latihan di IFGS Gym.
                    </p>
                    <a href="{{ route('member.paket-layanan') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1" style="font-size: 0.78rem;">
                        <i class="bx bx-plus-circle me-1"></i> Beli Paket Sekarang
                    </a>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-white border-opacity-10 text-white-50" style="font-size: 0.72rem;">
                <span>ID: {{ $member?->member_code ?? $user->user_code ?? '-' }}</span>
                <span>{{ $user->phone ?? 'IFGS Tondano' }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="row g-2 mb-3">
        <div class="col-3">
            <a href="{{ route('member.reservasi') }}" class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 40px; height: 40px;">
                    <i class="bx bx-calendar-plus fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Reservasi</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.riwayat') }}" class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 40px; height: 40px;">
                    <i class="bx bx-history fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Riwayat</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.paket-layanan') }}" class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 40px; height: 40px;">
                    <i class="bx bx-package fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Paket</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.profil') }}" class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 40px; height: 40px;">
                    <i class="bx bx-user fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Profil</span>
            </a>
        </div>
    </div>

    <!-- 2. RESERVASI TERDEKAT -->
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-calendar text-primary"></i> Reservasi Terdekat
            </h6>
            @if ($upcomingSchedule || $upcomingReservation)
                <a href="{{ route('member.reservasi') }}" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.75rem;">
                    Lihat Semua <i class="bx bx-chevron-right"></i>
                </a>
            @endif
        </div>
        <div class="card-body p-3">
            @if ($upcomingSchedule)
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded p-2 text-center" style="min-width: 48px;">
                            <span class="d-block fw-bold fs-6 lh-1">
                                {{ \Carbon\Carbon::parse($upcomingSchedule->scheduled_date)->format('d') }}
                            </span>
                            <small class="d-block text-uppercase" style="font-size: 0.65rem;">
                                {{ \Carbon\Carbon::parse($upcomingSchedule->scheduled_date)->translatedFormat('M') }}
                            </small>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">
                                {{ \Carbon\Carbon::parse($upcomingSchedule->scheduled_date)->translatedFormat('l, d F Y') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bx bx-time-five me-1 text-primary"></i>
                                {{ $upcomingSchedule->timeSlot?->formatted_time ?? 'Waktu disesuaikan' }}
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        Terjadwal
                    </span>
                </div>
            @elseif ($upcomingReservation)
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning text-white rounded p-2 text-center" style="min-width: 48px;">
                            <span class="d-block fw-bold fs-6 lh-1">
                                {{ \Carbon\Carbon::parse($upcomingReservation->visit_date)->format('d') }}
                            </span>
                            <small class="d-block text-uppercase" style="font-size: 0.65rem;">
                                {{ \Carbon\Carbon::parse($upcomingReservation->visit_date)->translatedFormat('M') }}
                            </small>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small">
                                {{ \Carbon\Carbon::parse($upcomingReservation->visit_date)->translatedFormat('l, d F Y') }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                <i class="bx bx-time-five me-1 text-warning"></i>
                                {{ $upcomingReservation->timeSlot?->formatted_time ?? 'Menunggu slot' }}
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                        Diproses
                    </span>
                </div>
            @else
                <div class="text-center py-3">
                    <div class="text-muted mb-2">
                        <i class="bx bx-calendar-x fs-1 text-secondary opacity-50"></i>
                    </div>
                    <p class="text-muted small mb-2">Belum ada reservasi aktif saat ini.</p>
                    <a href="{{ route('member.reservasi') }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill" style="font-size: 0.78rem;">
                        <i class="bx bx-plus me-1"></i> Reservasi Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. PAKET LAYANAN TERSEDIA -->
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-package text-primary"></i> Pilihan Paket Layanan
            </h6>
            <a href="{{ route('member.paket-layanan') }}" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.75rem;">
                Lihat Semua <i class="bx bx-chevron-right"></i>
            </a>
        </div>
        <div class="card-body p-3">
            @if ($featuredProducts->isNotEmpty())
                <div class="row g-2">
                    @foreach ($featuredProducts as $product)
                        <div class="col-12">
                            <div class="p-2 border rounded-3 d-flex align-items-center justify-content-between bg-white hover-shadow">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $product->name }}</h6>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">
                                        Durasi: {{ $product->duration_days }} hari @if($product->daily_sessions_limit) &bull; Max {{ $product->daily_sessions_limit }} sesi/hari @endif
                                    </small>
                                    <span class="fw-bold text-primary" style="font-size: 0.82rem;">
                                        {{ $product->formatted_price }}
                                    </span>
                                </div>
                                <a href="{{ route('member.paket-layanan') }}" class="btn btn-sm btn-light border px-2 py-1" style="font-size: 0.75rem;">
                                    Detail <i class="bx bx-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small text-center mb-0 py-2">Belum ada paket layanan aktif.</p>
            @endif
        </div>
    </div>
@endsection

