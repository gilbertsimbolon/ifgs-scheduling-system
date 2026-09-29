@extends('layouts.member')

@section('title', 'Riwayat & Log Aktivitas')

@section('content')
    <!-- Header Halaman -->
    <div class="mb-3">
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
            Riwayat Kunjungan & Aktivitas
        </h5>
        <small class="text-muted" style="font-size: 0.78rem;">
            Catatan langganan paket dan log riwayat aktivitas latihan Anda di IFGS Gym
        </small>
    </div>

    @php
        $activeMembershipsList = $myMemberships->filter(function ($ms) {
            return $ms->status === \App\Models\Membership::STATUS_ACTIVE &&
                (!$ms->end_date || $ms->end_date->isFuture() || $ms->end_date->isToday());
        });

        $inactiveMembershipsList = $myMemberships->reject(function ($ms) use ($activeMembershipsList) {
            return $activeMembershipsList->contains('id', $ms->id);
        });
    @endphp

    <!-- 1. CARD RIWAYAT LANGGANAN -->
    <div class="card border mb-3 shadow-sm bg-white" style="border-radius: 14px; border-color: #f1f5f9 !important;">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between"
            style="border-top-left-radius: 14px; border-top-right-radius: 14px; border-color: #f1f5f9 !important;">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-receipt text-danger"></i> Riwayat Langganan
            </h6>
            @if ($activeMembershipsList->isNotEmpty())
                <span
                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0"
                    style="font-size: 0.68rem; font-weight: 600;">
                    Paket Aktif
                </span>
            @elseif ($inactiveMembershipsList->isNotEmpty())
                <span
                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0"
                    style="font-size: 0.68rem; font-weight: 600;">
                    {{ $myMemberships->count() }} Riwayat
                </span>
            @endif
        </div>
        <div class="card-body p-3">
            <!-- Tampilkan Hanya Paket yang Aktif Saja Secara Terbuka -->
            @if ($activeMembershipsList->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach ($activeMembershipsList as $ms)
                        <div class="p-3 rounded-3 border bg-white shadow-none"
                            style="border-color: #fee2e2 !important; border-left: 4px solid #dc2626 !important; background: linear-gradient(135deg, #fffafa 0%, #ffffff 100%);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark fs-6">{{ $ms->product?->name ?? 'Paket Gym' }}</span>
                                @if ($ms->start_date && $ms->start_date->isFuture())
                                    <span
                                        class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1"
                                        style="font-size: 0.68rem;">
                                        <i class="bx bx-layer-plus me-1"></i>Perpanjangan (Mulai
                                        {{ $ms->start_date->format('d M Y') }})
                                    </span>
                                @else
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1"
                                        style="font-size: 0.68rem;">
                                        <i class="bx bx-check-circle me-1"></i>Aktif
                                    </span>
                                @endif
                            </div>

                            <div class="p-2 rounded-2 mb-2" style="background-color: #fff5f5; border: 1px solid #fee2e2;">
                                <div class="d-flex align-items-center justify-content-between small">
                                    <span class="text-muted" style="font-size: 0.72rem;">Metode Pembayaran:</span>
                                    <span class="fw-bold text-dark" style="font-size: 0.75rem;">
                                        {{ $ms->paymentMethod?->name ?? 'Transfer' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between small mt-1">
                                    <span class="text-muted" style="font-size: 0.72rem;">Total Bayar:</span>
                                    <span class="fw-bold text-danger" style="font-size: 0.78rem;">
                                        {{ $ms->formatted_price }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted small"
                                style="font-size: 0.72rem;">
                                <span>
                                    <i class="bx bx-calendar me-1 text-danger"></i>
                                    {{ $ms->start_date ? $ms->start_date->format('d M Y') : '-' }} s/d
                                    {{ $ms->end_date ? $ms->end_date->format('d M Y') : '-' }}
                                </span>
                                <span>Diajukan: {{ $ms->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-3 text-center rounded-3 bg-light border border-dashed mb-2">
                    <span class="text-muted small d-block">Tidak ada paket membership yang sedang aktif.</span>
                    <a href="{{ route('member.paket-layanan') }}" class="btn btn-sm btn-danger rounded-pill px-3 py-1 mt-2"
                        style="font-size: 0.75rem;">
                        <i class="bx bx-cart me-1"></i> Beli Paket Membership
                    </a>
                </div>
            @endif

            <!-- Jika Tidak Aktif / Kedaluwarsa, Buat Seperti FAQ (Accordion Collapsible) -->
            @if ($inactiveMembershipsList->isNotEmpty())
                <div class="mt-2">
                    <div class="accordion accordion-flush" id="faqPastMemberships">
                        <div class="accordion-item border rounded-3 overflow-hidden"
                            style="border-color: #f1f5f9 !important;">
                            <h2 class="accordion-header" id="headingFaqPast">
                                <button class="accordion-button collapsed py-2 px-3 bg-light text-dark fw-bold small"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqPast"
                                    aria-expanded="false" aria-controls="collapseFaqPast"
                                    style="font-size: 0.78rem; background-color: #f8fafc;">
                                    <div class="d-flex align-items-center justify-content-between w-100 me-2">
                                        <span class="d-flex align-items-center gap-1 text-secondary">
                                            <i class="bx bx-help-circle fs-5 text-danger"></i>
                                            Lihat Paket Sebelumnya (FAQ)
                                        </span>
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0"
                                            style="font-size: 0.65rem;">
                                            {{ $inactiveMembershipsList->count() }} Paket
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseFaqPast" class="accordion-collapse collapse" aria-labelledby="headingFaqPast"
                                data-bs-parent="#faqPastMemberships">
                                <div class="accordion-body p-2 bg-white d-flex flex-column gap-2">
                                    @foreach ($inactiveMembershipsList as $index => $ms)
                                        <div class="p-2 rounded-2 border"
                                            style="background-color: #fcfcfd; border-color: #f1f5f9 !important;">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-bold text-dark" style="font-size: 0.82rem;">
                                                    {{ $ms->product?->name ?? 'Paket Gym' }}
                                                </span>
                                                <div>
                                                    @if ($ms->status === \App\Models\Membership::STATUS_EXPIRED || ($ms->end_date && $ms->end_date->isPast()))
                                                        <span
                                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0"
                                                            style="font-size: 0.62rem;">Kedaluwarsa</span>
                                                    @elseif ($ms->status === \App\Models\Membership::STATUS_PENDING)
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-0"
                                                            style="font-size: 0.62rem;">Pending</span>
                                                    @elseif ($ms->status === \App\Models\Membership::STATUS_REJECTED)
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0"
                                                            style="font-size: 0.62rem;">Ditolak</span>
                                                    @else
                                                        <span
                                                            class="badge bg-light text-muted border rounded-pill px-2 py-0"
                                                            style="font-size: 0.62rem;">{{ ucfirst($ms->status) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between text-muted"
                                                style="font-size: 0.70rem;">
                                                <span>{{ $ms->paymentMethod?->name ?? 'Transfer' }} &bull; <strong
                                                        class="text-danger">{{ $ms->formatted_price }}</strong></span>
                                                <span>Diajukan: {{ $ms->created_at->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="text-muted mt-1" style="font-size: 0.68rem;">
                                                <i class="bx bx-calendar me-1"></i> Periode:
                                                {{ $ms->start_date ? $ms->start_date->format('d/m/Y') : '-' }} s/d
                                                {{ $ms->end_date ? $ms->end_date->format('d/m/Y') : '-' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 2. CARD LOG AKTIVITAS (Scrollable Khusus Bagian Ini Tanpa Ngescroll Halaman) -->
    <div class="card border mb-3 shadow-sm bg-white" style="border-radius: 14px; border-color: #f1f5f9 !important;">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between"
            style="border-top-left-radius: 14px; border-top-right-radius: 14px; border-color: #f1f5f9 !important;">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-history text-danger"></i> Log Aktivitas
            </h6>
            @if (isset($activityLogs) && $activityLogs->isNotEmpty())
                <span
                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0"
                    style="font-size: 0.68rem; font-weight: 600;">
                    {{ $activityLogs->count() }} Aktivitas
                </span>
            @endif
        </div>
        <div class="card-body p-3">
            @if (isset($activityLogs) && $activityLogs->isNotEmpty())
                <!-- Container Scrollable Khusus Aktivitas (Internal Scroll) -->
                <div class="d-flex flex-column gap-2 activity-scroll-container">
                    @foreach ($activityLogs as $act)
                        <div class="p-3 rounded-3 border bg-white shadow-none" style="border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 36px; height: 36px; background-color: {{ $act['icon_bg'] }};">
                                        <i class="bx {{ $act['icon'] }} fs-5 {{ $act['icon_color'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold text-dark small"
                                                style="font-size: 0.85rem;">{{ $act['title'] }}</span>
                                            <span
                                                class="badge {{ $act['type'] === 'attendance' ? 'bg-success' : 'bg-primary' }} bg-opacity-10 {{ $act['type'] === 'attendance' ? 'text-success' : 'text-primary' }} rounded-pill px-2"
                                                style="font-size: 0.62rem;">
                                                {{ $act['type'] === 'attendance' ? 'Kehadiran' : 'Jadwal' }}
                                            </span>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                            {{ $act['date']->translatedFormat('l, d F Y') }}
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge {{ $act['status_badge_class'] }} rounded-pill px-2 py-1"
                                        style="font-size: 0.68rem;">
                                        {{ $act['status_label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Detail Info Box -->
                            <div class="p-2 rounded-2 small text-muted"
                                style="background-color: #f8fafc; border: 1px solid #f1f5f9; font-size: 0.72rem;">
                                @if ($act['type'] === 'attendance')
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span><i class="bx bx-log-in-circle me-1 text-success"></i> Check-in: <strong
                                                class="text-dark">{{ $act['check_in_at'] }} WITA</strong></span>
                                        @if ($act['check_out_at'])
                                            <span><i class="bx bx-log-out-circle me-1 text-secondary"></i> Check-out:
                                                <strong class="text-dark">{{ $act['check_out_at'] }} WITA</strong></span>
                                        @else
                                            <span class="text-success fw-semibold"><i
                                                    class="bx bx-radio-circle-marked"></i>
                                                Sedang di Gym</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span><i class="bx bx-time me-1 text-primary"></i> Sesi: <strong
                                                class="text-dark">{{ $act['time_slot'] }}</strong></span>
                                        <span>Status: <strong class="text-dark">{{ $act['status_label'] }}</strong></span>
                                    </div>
                                @endif
                            </div>

                            @if (!empty($act['notes']))
                                <div class="mt-2 text-muted small" style="font-size: 0.7rem;">
                                    <i class="bx bx-comment-detail me-1"></i> {{ $act['notes'] }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if ($activityLogs->count() > 3)
                    <div class="text-center mt-2">
                        <small class="text-muted" style="font-size: 0.68rem;">
                            <i class="bx bx-mouse me-1"></i> Gulir ke bawah pada daftar aktivitas di atas
                        </small>
                    </div>
                @endif
            @else
                <div class="p-4 text-center">
                    <i class="bx bx-history fs-1 text-secondary opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Log Aktivitas</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        Catatan absensi gym dan riwayat jadwal latihan Anda akan otomatis tercatat di sini.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .activity-scroll-container {
            max-height: 380px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .activity-scroll-container::-webkit-scrollbar {
            width: 5px;
        }

        .activity-scroll-container::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 8px;
        }

        .activity-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .activity-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush
