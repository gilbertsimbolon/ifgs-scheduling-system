@extends('layouts.member')

@section('title', 'Riwayat Kunjungan & Absensi')

@section('content')
    <!-- Header Halaman -->
    <div class="mb-3">
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
            Riwayat Kunjungan
        </h5>
        <small class="text-muted" style="font-size: 0.78rem;">
            Catatan kehadiran latihan dan jadwal masa lalu di IFGS Gym
        </small>
    </div>

    <!-- Nav Pills / Tabs: Langganan Paket, Presensi Gym, & Riwayat Jadwal -->
    <ul class="nav nav-pills nav-fill mb-3 bg-white p-1 rounded-3 border shadow-sm" id="riwayatTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active py-2 fw-semibold rounded-2" id="membership-tab" data-bs-toggle="pill" data-bs-target="#membership-pane" type="button" role="tab" aria-controls="membership-pane" aria-selected="true" style="font-size: 0.75rem;">
                <i class="bx bx-receipt me-1"></i> Langganan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 fw-semibold rounded-2" id="presensi-tab" data-bs-toggle="pill" data-bs-target="#presensi-pane" type="button" role="tab" aria-controls="presensi-pane" aria-selected="false" style="font-size: 0.75rem;">
                <i class="bx bx-check-circle me-1"></i> Kehadiran Gym
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 fw-semibold rounded-2" id="jadwal-tab" data-bs-toggle="pill" data-bs-target="#jadwal-pane" type="button" role="tab" aria-controls="jadwal-pane" aria-selected="false" style="font-size: 0.75rem;">
                <i class="bx bx-calendar-event me-1"></i> Jadwal Selesai
            </button>
        </li>
    </ul>

    <div class="tab-content" id="riwayatTabContent">
        <!-- 1. Tab Riwayat Langganan Membership -->
        <div class="tab-pane fade show active" id="membership-pane" role="tabpanel" aria-labelledby="membership-tab" tabindex="0">
            @if (isset($myMemberships) && $myMemberships->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach ($myMemberships as $ms)
                        <div class="card mb-0 p-3 bg-white border hover-shadow" style="border-radius: 12px; border-color: #f1f5f9 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark fs-6">{{ $ms->product?->name ?? 'Paket Gym' }}</span>
                                <div>
                                    @if ($ms->status === \App\Models\Membership::STATUS_ACTIVE)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"
                                            style="font-size: 0.68rem;">Aktif</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_PENDING)
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1"
                                            style="font-size: 0.68rem;">Pending</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_REJECTED)
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1"
                                            style="font-size: 0.68rem;">Ditolak</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_EXPIRED)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1"
                                            style="font-size: 0.68rem;">Kedaluwarsa</span>
                                    @else
                                        <span class="badge bg-light text-muted rounded-pill px-2 py-1"
                                            style="font-size: 0.68rem;">{{ ucfirst($ms->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="p-2 rounded-2 mb-2" style="background-color: #fff5f5; border: 1px solid #fee2e2;">
                                <div class="d-flex align-items-center justify-content-between small">
                                    <span class="text-muted" style="font-size: 0.72rem;">Metode Pembayaran:</span>
                                    <span class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $ms->paymentMethod?->name ?? 'Transfer' }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between small mt-1">
                                    <span class="text-muted" style="font-size: 0.72rem;">Total Bayar:</span>
                                    <span class="fw-bold text-danger" style="font-size: 0.78rem;">{{ $ms->formatted_price }}</span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.72rem;">
                                <span><i class="bx bx-calendar me-1"></i> {{ $ms->start_date ? $ms->start_date->format('d M Y') : '-' }} s/d {{ $ms->end_date ? $ms->end_date->format('d M Y') : '-' }}</span>
                                <span>Diajukan: {{ $ms->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-4 text-center border bg-white" style="border-radius: 12px;">
                    <i class="bx bx-receipt fs-1 text-secondary opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Riwayat Langganan</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        Riwayat pembelian paket membership Anda akan tercatat otomatis di sini.
                    </p>
                </div>
            @endif
        </div>

        <!-- 2. Tab Kehadiran / Absensi -->
        <div class="tab-pane fade" id="presensi-pane" role="tabpanel" aria-labelledby="presensi-tab" tabindex="0">
            @if ($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach ($attendances as $att)
                        <div class="card mb-0 p-3 bg-white border hover-shadow">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded p-2 text-center" style="min-width: 44px;">
                                        <span class="d-block fw-bold fs-6 lh-1">
                                            {{ \Carbon\Carbon::parse($att->date)->format('d') }}
                                        </span>
                                        <small class="d-block text-uppercase" style="font-size: 0.65rem;">
                                            {{ \Carbon\Carbon::parse($att->date)->translatedFormat('M') }}
                                        </small>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">
                                            {{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d F Y') }}
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.72rem;">
                                            Check-in: <strong class="text-success">{{ $att->check_in_at ? \Carbon\Carbon::parse($att->check_in_at)->format('H:i') : '-' }}</strong>
                                            @if ($att->check_out_at)
                                                &bull; Check-out: <strong class="text-secondary">{{ \Carbon\Carbon::parse($att->check_out_at)->format('H:i') }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if ($att->isCurrentlyInGym())
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                            Aktif di Gym
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                            Selesai
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($att->notes)
                                <div class="bg-light p-2 rounded-2 text-muted small mt-1" style="font-size: 0.72rem;">
                                    <i class="bx bx-comment-detail me-1"></i> {{ $att->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $attendances->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="card p-4 text-center border">
                    <i class="bx bx-calendar-check fs-1 text-secondary opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Riwayat Kehadiran</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        Data absensi akan tercatat otomatis saat Anda melakukan check-in di gym menggunakan kartu atau QR member.
                    </p>
                </div>
            @endif
        </div>

        <!-- 2. Tab Jadwal Masa Lalu -->
        <div class="tab-pane fade" id="jadwal-pane" role="tabpanel" aria-labelledby="jadwal-tab" tabindex="0">
            @if ($pastSchedules->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach ($pastSchedules as $sched)
                        <div class="card mb-0 p-3 bg-white border hover-shadow">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="fw-bold text-dark small">
                                    {{ \Carbon\Carbon::parse($sched->scheduled_date)->translatedFormat('l, d F Y') }}
                                </div>
                                <div>
                                    @if ($sched->status === \App\Models\Schedule::STATUS_ATTENDED)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.68rem;">Hadir</span>
                                    @elseif ($sched->status === \App\Models\Schedule::STATUS_NO_SHOW)
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill" style="font-size: 0.68rem;">Tidak Hadir</span>
                                    @elseif ($sched->status === \App\Models\Schedule::STATUS_CANCELLED)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.68rem;">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill" style="font-size: 0.68rem;">Selesai</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                <i class="bx bx-time-five me-1"></i> Slot: {{ $sched->timeSlot?->formatted_time ?? 'Waktu disesuaikan' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card p-4 text-center border">
                    <i class="bx bx-history fs-1 text-secondary opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Riwayat Jadwal</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        Riwayat jadwal latihan yang telah lewat akan tampil di bagian ini.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

