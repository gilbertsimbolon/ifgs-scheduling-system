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
                Halo, {{ $user->name }}
            </h5>
        </div>
        <div>
            @if ($currentAttendance)
                <span
                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill small">
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
                    <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style="width: 38px; height: 38px;">
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
        <div class="position-absolute end-0 top-0 translate-middle-y opacity-10"
            style="margin-right: -20px; margin-top: 10px;">
            <i class="bx bx-dumbbell" style="font-size: 9rem; color: #ffffff;"></i>
        </div>

        <div class="card-body p-3 p-sm-4 position-relative" style="z-index: 2;">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" width="34" height="34"
                        class="rounded-circle border border-white border-2" />
                    <div>
                        <span class="fw-bold text-white small d-block lh-1" style="letter-spacing: 0.5px;">INDO FITNESS
                            GYM</span>
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
                    <a href="{{ route('member.paket-layanan') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1"
                        style="font-size: 0.78rem;">
                        <i class="bx bx-plus-circle me-1"></i> Beli Paket Sekarang
                    </a>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top border-white border-opacity-10 text-white-50"
                style="font-size: 0.72rem;">
                <span>ID: {{ $member?->member_code ?? ($user->user_code ?? '-') }}</span>
                <span>{{ $user->phone ?? 'IFGS Tondano' }}</span>
            </div>
        </div>
    </div>

    <!-- Status & Kondisi Keramaian Gym Real-time Hari Ini -->
    @php
        $level = $crowdMetrics['crowd_level']['level'] ?? 'sepi';
        $levelConfigs = [
            'sepi' => [
                'bg' => 'linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%)',
                'border' => '#a7f3d0',
                'avatar_bg' => '#d1fae5',
                'avatar_color' => '#047857',
                'avatar_border' => '#86efac',
                'title_color' => '#065f46',
                'sub_color' => '#047857',
                'num_color' => '#065f46',
                'bar_track' => '#d1fae5',
                'bar_fill' => 'linear-gradient(90deg, #10b981, #059669)',
                'badge_bg' => '#dcfce7',
                'badge_color' => '#15803d',
                'badge_border' => '#86efac',
            ],
            'sedang' => [
                'bg' => 'linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%)',
                'border' => '#bfdbfe',
                'avatar_bg' => '#dbeafe',
                'avatar_color' => '#1d4ed8',
                'avatar_border' => '#93c5fd',
                'title_color' => '#1e3a8a',
                'sub_color' => '#1d4ed8',
                'num_color' => '#1e3a8a',
                'bar_track' => '#dbeafe',
                'bar_fill' => 'linear-gradient(90deg, #3b82f6, #1d4ed8)',
                'badge_bg' => '#dbeafe',
                'badge_color' => '#1e40af',
                'badge_border' => '#93c5fd',
            ],
            'ramai' => [
                'bg' => 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)',
                'border' => '#fecaca',
                'avatar_bg' => '#fee2e2',
                'avatar_color' => '#b91c1c',
                'avatar_border' => '#fca5a5',
                'title_color' => '#991b1b',
                'sub_color' => '#b91c1c',
                'num_color' => '#7f1d1d',
                'bar_track' => '#fecaca',
                'bar_fill' => 'linear-gradient(90deg, #ef4444, #b91c1c)',
                'badge_bg' => '#fee2e2',
                'badge_color' => '#991b1b',
                'badge_border' => '#fca5a5',
            ],
            'closed' => [
                'bg' => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)',
                'border' => '#fecdd3',
                'avatar_bg' => '#ffe4e6',
                'avatar_color' => '#be123c',
                'avatar_border' => '#fda4af',
                'title_color' => '#9f1239',
                'sub_color' => '#be123c',
                'num_color' => '#881337',
                'bar_track' => '#fecdd3',
                'bar_fill' => '#e11d48',
                'badge_bg' => '#ffe4e6',
                'badge_color' => '#9f1239',
                'badge_border' => '#fda4af',
            ],
        ];
        $currentCfg = $levelConfigs[$level] ?? $levelConfigs['sepi'];
    @endphp

    <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden bg-white"
        style="border: 1px solid #fee2e2 !important;">
        <!-- Header -->
        <div class="py-3 px-3 d-flex align-items-center justify-content-between"
            style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border-bottom: 1px solid #fee2e2;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 34px; height: 34px; background: #dc2626; color: #ffffff; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);">
                    <i class="bx bx-broadcast fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark fs-6" style="letter-spacing: -0.2px;">Kondisi & Keramaian Gym</h6>
                    <small class="text-danger fw-semibold" style="font-size: 0.68rem;">Live Monitoring Hari Ini</small>
                </div>
            </div>
            <div>
                @if ($gymStatus['is_open'] ?? false)
                    <span class="badge rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2 fw-bold shadow-xs"
                        style="font-size: 0.72rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                        <span class="live-dot"></span> Buka Sekarang
                    </span>
                @else
                    <span class="badge rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 fw-bold shadow-xs"
                        style="font-size: 0.72rem; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">
                        <i class="bx bx-time me-1"></i> {{ $gymStatus['status_label'] ?? 'Tutup' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body p-3">
            <!-- Meter Keramaian Utama -->
            <div class="p-3 rounded-3 mb-0 shadow-xs"
                style="background: {{ $currentCfg['bg'] }}; border: 1px solid {{ $currentCfg['border'] }};">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                            style="width: 38px; height: 38px; background: {{ $currentCfg['avatar_bg'] }}; color: {{ $currentCfg['avatar_color'] }}; border: 1px solid {{ $currentCfg['avatar_border'] }};">
                            <i class="bx {{ $crowdMetrics['crowd_level']['icon'] ?? 'bx-smile' }} fs-4"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold"
                                style="font-size: 0.92rem; color: {{ $currentCfg['title_color'] }};">
                                {{ $crowdMetrics['crowd_level']['label'] ?? 'Normal' }}
                            </span>
                            <small class="d-block fw-medium"
                                style="font-size: 0.72rem; line-height: 1.25; color: {{ $currentCfg['sub_color'] }};">
                                {{ $crowdMetrics['crowd_level']['description'] ?? 'Suasana latihan kondusif.' }}
                            </small>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="fw-bold fs-4 lh-1 d-block" style="color: {{ $currentCfg['num_color'] }};">
                            {{ $crowdMetrics['in_gym_count'] ?? 0 }}
                        </span>
                        <small class="fw-semibold" style="font-size: 0.7rem; color: {{ $currentCfg['sub_color'] }};">
                            / {{ $crowdMetrics['total_capacity'] ?? 100 }} Kapasitas
                        </small>
                    </div>
                </div>

                <!-- Progress Bar Okupansi -->
                <div class="rounded-pill mb-1 p-0 overflow-hidden"
                    style="height: 10px; background-color: {{ $currentCfg['bar_track'] }};">
                    <div class="h-100 rounded-pill" role="progressbar"
                        style="width: {{ $crowdMetrics['percentage'] ?? 0 }}%; background: {{ $currentCfg['bar_fill'] }}; transition: width 0.4s ease;"
                        aria-valuenow="{{ $crowdMetrics['percentage'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1"
                    style="font-size: 0.7rem; color: {{ $currentCfg['sub_color'] }};">
                    <span class="fw-bold">Tingkat Kepadatan: {{ $crowdMetrics['percentage'] ?? 0 }}%</span>
                    <span class="badge rounded-pill px-2 py-0 fw-bold"
                        style="background: {{ $currentCfg['badge_bg'] }}; color: {{ $currentCfg['badge_color'] }}; border: 1px solid {{ $currentCfg['badge_border'] }};">
                        <i class="bx bx-check-shield me-1"></i>Real-time Hari Ini
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Grid -->
    <div class="row g-2 mb-3">
        <div class="col-3">
            <a href="{{ route('member.reservasi') }}"
                class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-1"
                    style="width: 40px; height: 40px;">
                    <i class="bx bx-calendar-plus fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Reservasi</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.riwayat') }}"
                class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center mb-1"
                    style="width: 40px; height: 40px;">
                    <i class="bx bx-history fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Riwayat</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.paket-layanan') }}"
                class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center mb-1"
                    style="width: 40px; height: 40px;">
                    <i class="bx bx-package fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Paket</span>
            </a>
        </div>
        <div class="col-3">
            <a href="{{ route('member.profil') }}"
                class="card text-center text-decoration-none p-2 h-100 mb-0 shadow-none border hover-shadow">
                <div class="mx-auto bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center mb-1"
                    style="width: 40px; height: 40px;">
                    <i class="bx bx-user fs-5"></i>
                </div>
                <span class="text-dark small fw-semibold" style="font-size: 0.7rem;">Profil</span>
            </a>
        </div>
    </div>

    <!-- 2. RESERVASI TERDEKAT -->
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div
            class="card-header d-flex align-items-center justify-content-between py-2 px-3 bg-white border-bottom rounded-top-3">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-calendar text-danger"></i> Reservasi Terdekat
            </h6>
            @if ($upcomingSchedule || $upcomingReservation)
                <a href="{{ route('member.reservasi') }}" class="text-danger text-decoration-none small fw-semibold"
                    style="font-size: 0.75rem;">
                    Lihat Semua <i class="bx bx-chevron-right"></i>
                </a>
            @endif
        </div>
        <div class="card-body p-3">
            @if ($upcomingSchedule)
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-white border shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger text-white rounded-3 p-2 text-center shadow-xs" style="min-width: 48px;">
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
                            <div class="text-danger small fw-semibold" style="font-size: 0.75rem;">
                                <i class="bx bx-time-five me-1"></i>
                                {{ $upcomingSchedule->timeSlot?->time_range ?? '08:00 - 20:00' }}
                            </div>
                        </div>
                    </div>
                    <span
                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1"
                        style="font-size: 0.7rem;">
                        <i class="bx bx-check-circle me-1"></i>Terjadwal
                    </span>
                </div>
            @elseif ($upcomingReservation)
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-white border shadow-xs">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning text-white rounded-3 p-2 text-center shadow-xs" style="min-width: 48px;">
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
                            <div class="text-warning small fw-semibold" style="font-size: 0.75rem;">
                                <i class="bx bx-time-five me-1"></i>
                                {{ $upcomingReservation->timeSlot?->time_range ?? 'Menunggu slot' }}
                            </div>
                        </div>
                    </div>
                    <span
                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1"
                        style="font-size: 0.7rem;">
                        <i class="bx bx-time me-1"></i>Diproses
                    </span>
                </div>
            @else
                <div class="text-center py-3">
                    <div class="avatar avatar-md bg-danger bg-opacity-10 text-danger rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                        style="width: 44px; height: 44px;">
                        <i class="bx bx-calendar-plus fs-4"></i>
                    </div>
                    <p class="text-muted small mb-2">Belum ada reservasi aktif saat ini.</p>
                    <a href="{{ route('member.reservasi') }}" class="btn btn-outline-danger btn-sm px-3 rounded-pill"
                        style="font-size: 0.78rem;">
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
            <a href="{{ route('member.paket-layanan') }}" class="text-primary text-decoration-none small fw-semibold"
                style="font-size: 0.75rem;">
                Lihat Semua <i class="bx bx-chevron-right"></i>
            </a>
        </div>
        <div class="card-body p-3">
            @if ($featuredProducts->isNotEmpty())
                <div class="row g-2">
                    @foreach ($featuredProducts as $product)
                        <div class="col-12">
                            <div
                                class="p-2 border rounded-3 d-flex align-items-center justify-content-between bg-white hover-shadow">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $product->name }}
                                    </h6>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">
                                        Durasi: {{ $product->duration_days }} hari @if ($product->daily_sessions_limit)
                                            &bull; Max {{ $product->daily_sessions_limit }} sesi/hari
                                        @endif
                                    </small>
                                    <span class="fw-bold text-primary" style="font-size: 0.82rem;">
                                        {{ $product->formatted_price }}
                                    </span>
                                </div>
                                <a href="{{ route('member.paket-layanan') }}"
                                    class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 0.75rem;">
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

@push('styles')
    <style>
        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .shadow-xs {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush
