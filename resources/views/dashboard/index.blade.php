@extends('layouts.app')

@section('title', 'Dashboard - IFGS Scheduling System')

@section('content')
    <div class="container-xxl flex-grow-1">
        @if ($isMember)
            <!-- Member Dashboard View -->
            <!-- Welcome Banner -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="text-white fw-bold mb-1">Halo, {{ auth()->user()->name }}! 👋</h4>
                            <p class="mb-0 text-white-50">Selamat datang di portal reservasi Indo Fitness Gym Sport Tondano.
                            </p>
                        </div>
                        <div>
                            @if ($activeMembership)
                                <a href="{{ route('reservations.index') }}" class="btn btn-light text-primary fw-semibold">
                                    <i class="bx bx-calendar-plus me-1"></i> Reservasi Kunjungan Baru
                                </a>
                            @else
                                <span class="badge bg-warning text-dark p-2">Membership Anda Belum Aktif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal Operasional Gym Resmi IFGS -->
            @include('dashboard.partials.operational-schedule')

            <div class="row g-4 mb-4">
                <!-- Status Membership Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <span class="badge bg-label-primary mb-2">Paket Membership Anda</span>
                            @if ($activeMembership)
                                <h4 class="card-title fw-bold text-primary mb-1">
                                    {{ $activeMembership->product->name ?? 'Membership Reguler' }}</h4>
                                <p class="text-muted small mb-3">Kode Member: <strong
                                        class="text-dark">{{ $member->member_code ?? '-' }}</strong></p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted">Masa Berlaku:</small>
                                    <span class="badge bg-label-success">s/d
                                        {{ $activeMembership->end_date->format('d M Y') }}</span>
                                </div>
                            @else
                                <h5 class="text-muted mb-2">Belum Memiliki Paket Aktif</h5>
                                <p class="text-muted small">Silakan hubungi kasir/pengelola gym untuk mengaktifkan paket
                                    membership Anda.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Total Reservasi Saya -->
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-label-info">Riwayat Reservasi</span>
                                <i class="bx bx-calendar-event fs-3 text-info"></i>
                            </div>
                            <h2 class="fw-bold mb-1">{{ $myReservationsCount }}</h2>
                            <p class="text-muted small mb-0">Total reservasi sesi latihan yang pernah Anda ajukan.</p>
                        </div>
                    </div>
                </div>

                <!-- Alur Kunjungan Member -->
                <div class="col-md-12 col-lg-4">
                    <div class="card h-100 shadow-sm bg-lighter">
                        <div class="card-body">
                            <h6 class="fw-bold mb-2 text-primary"><i class="bx bx-info-circle me-1"></i> Alur Kunjungan Gym
                            </h6>
                            <ol class="ps-3 mb-0 small text-muted">
                                <li class="mb-1">Pastikan membership aktif.</li>
                                <li class="mb-1">Pilih tanggal rencana olahraga.</li>
                                <li class="mb-1">Algoritma Greedy mengalokasikan sesi waktu optimal.</li>
                                <li>Datang sesuai jadwal & check-in di meja resepsionis.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal Kunjungan Mendatang -->
            <div class="card shadow-sm mb-4">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 text-heading">
                        <i class="bx bx-calendar-star me-1 text-primary"></i> Jadwal Kunjungan Mendatang Anda
                    </h5>
                    <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Jadwal</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Jadwal</th>
                                <th>Tanggal</th>
                                <th>Sesi / Waktu</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myUpcomingSchedules as $sch)
                                <tr>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $sch->schedule_code }}</span>
                                    </td>
                                    <td>{{ $sch->scheduled_date->format('d M Y') }}
                                        ({{ $sch->scheduled_date->translatedFormat('l') }})</td>
                                    <td>
                                        <span class="badge bg-label-primary">
                                            {{ $sch->timeSlot->name ?? '-' }} ({{ $sch->timeSlot->time_range ?? '-' }})
                                        </span>
                                    </td>
                                    <td><span class="badge bg-label-success">Terjadwal</span></td>
                                    <td><small
                                            class="text-muted">{{ $sch->notes ?? 'Dialokasikan optimal oleh Algoritma Greedy' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Anda belum memiliki jadwal kunjungan mendatang. Silakan lakukan reservasi terlebih
                                        dahulu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Admin / Manager Dashboard View -->
            <!-- Welcome Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-2 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold py-1 mb-0">
                        Dashboard Penjadwalan Gym IFGS
                    </h4>
                    <p class="text-muted mb-0">
                        Sistem Informasi Optimasi Penjadwalan Kunjungan Member Berbasis Algoritma Greedy &bull;
                        {{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('reservations.index') }}" class="btn btn-primary">
                        <i class="bx bx-calendar-plus me-1"></i> Buat Reservasi
                    </a>
                    <a href="{{ route('schedules.index') }}" class="btn btn-outline-primary">
                        <i class="bx bx-calendar-check me-1"></i> Monitoring Jadwal
                    </a>
                </div>
            </div>

            <!-- Jadwal Operasional Gym Resmi IFGS -->
            @include('dashboard.partials.operational-schedule')

            <!-- Ringkasan Membership & Pendapatan Bulanan (Dipindahkan dari Membership) -->
            <div class="row g-3 mb-4">
                <!-- Total Transaksi -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left">
                                    <span class="text-muted fw-semibold">Total Transaksi</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2">{{ number_format($totalMembershipTransactions) }}</h4>
                                    </div>
                                    <small class="text-muted">Semua riwayat paket</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-credit-card bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Aktif -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left">
                                    <span class="text-muted fw-semibold">Membership Aktif</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2 text-success">{{ number_format($activeMemberships) }}</h4>
                                    </div>
                                    <small class="text-success">Dapat beraktivitas</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-check-shield bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kadaluarsa -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-start border-warning border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left">
                                    <span class="text-muted fw-semibold">Kadaluarsa</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h4 class="mb-0 me-2 text-warning">{{ number_format($expiredMemberships) }}</h4>
                                    </div>
                                    <small class="text-warning">Perlu perpanjangan</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-time-five bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Pendapatan Bulan Ini -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="content-left">
                                    <span class="text-muted fw-semibold">Pendapatan Bulan Ini</span>
                                    <div class="d-flex align-items-center my-1">
                                        <h5 class="mb-0 me-2 text-heading text-primary fw-bold">Rp
                                            {{ number_format($monthlyRevenue, 0, ',', '.') }}</h5>
                                    </div>
                                    <small class="text-muted">{{ $currentMonthLabel }}</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-wallet bx-sm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row g-3 mb-4">
                <!-- Total Member -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-border-shadow-primary h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-group fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ $totalMembers }}</h4>
                                    <small class="text-muted">Total Member</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="text-muted">Member Aktif:</span>
                                <span class="badge bg-label-success">{{ $activeMembers }} Orang</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Aktif -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-border-shadow-success h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-id-card fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ $activeMemberships }}</h4>
                                    <small class="text-muted">Membership Aktif</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="text-muted">Siap Reservasi:</span>
                                <span class="badge bg-label-primary">{{ $activeMemberships }} Member</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservasi Hari Ini -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-border-shadow-warning h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-calendar-event fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ $todayReservationsCount }}</h4>
                                    <small class="text-muted">Reservasi Hari Ini</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="text-muted">Terjadwal vs Pending:</span>
                                <span class="badge bg-label-{{ $todayPendingReservations > 0 ? 'warning' : 'success' }}">
                                    {{ $todayScheduledReservations }} Terjadwal / {{ $todayPendingReservations }} Pending
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kehadiran Gym Hari Ini -->
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-border-shadow-info h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-user-check fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ $todayAttendedCount }}</h4>
                                    <small class="text-muted">Check-in / Hadir</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                                <span class="text-muted">Dari Target Terjadwal:</span>
                                <span class="badge bg-label-info">{{ $todayTotalVisits }} Orang</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flow Banner -->
            <div class="card bg-lighter border mb-4">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="fw-bold small text-muted text-uppercase">Arsitektur Alur Utama Sistem:</span>
                        <div class="d-flex align-items-center flex-wrap gap-2 small">
                            <span class="badge bg-primary">MEMBER</span>
                            <i class="bx bx-right-arrow-alt text-muted"></i>
                            <span class="badge bg-info">MEMBERSHIP</span>
                            <i class="bx bx-right-arrow-alt text-muted"></i>
                            <span class="badge bg-warning">RESERVASI</span>
                            <i class="bx bx-right-arrow-alt text-muted"></i>
                            <span class="badge bg-secondary">TIME SLOT</span>
                            <i class="bx bx-right-arrow-alt text-muted"></i>
                            <span class="badge bg-dark">ALGORITMA GREEDY</span>
                            <i class="bx bx-right-arrow-alt text-muted"></i>
                            <span class="badge bg-success">JADWAL KUNJUNGAN</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Slot Occupancy Distribution Monitor (Load Balancing Monitor) -->
                <div class="col-12 col-lg-7">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold mb-0 text-heading">
                                    <i class="bx bx-bar-chart-alt-2 me-1 text-primary"></i> Okupansi Time Slot Hari Ini
                                </h5>
                                <small class="text-muted">Distribusi beban kunjungan gym Indo Fitness Tondano</small>
                            </div>
                            <form action="{{ route('schedules.optimize') }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $today }}">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="bx bx-brain me-1"></i> Optimasi Greedy
                                </button>
                            </form>
                        </div>
                        <div class="card-body p-3">
                            @forelse ($slotOccupancies as $occ)
                                @php
                                    $pct = $occ['percentage'];
                                    $barColor = $pct >= 100 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-semibold text-heading small">{{ $occ['slot']->name }}</span>
                                            <span class="badge bg-label-dark font-monospace ms-1"
                                                style="font-size: 0.72rem;">{{ $occ['slot']->time_range }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold small">{{ $occ['occupied'] }} /
                                                {{ $occ['slot']->capacity }} org</span>
                                            <span class="badge {{ $barColor }} ms-1"
                                                style="font-size: 0.7rem;">{{ $pct }}%</span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $barColor }}" role="progressbar"
                                            style="width: {{ min(100, $pct) }}%;" aria-valuenow="{{ $pct }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    Belum ada Time Slot aktif.
                                </div>
                            @endforelse
                        </div>
                        <div
                            class="card-footer border-top bg-lighter py-2 d-flex justify-content-between align-items-center small">
                            <span class="text-muted">Total Kapasitas Kuota Gym Hari Ini: <strong>{{ $totalGymCapacity }}
                                    Orang</strong></span>
                            <a href="{{ route('schedules.index') }}" class="fw-semibold text-primary">Lihat Matriks
                                Jadwal Lengkap &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Kunjungan Terjadwal Hari Ini -->
                <div class="col-12 col-lg-5">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold mb-0 text-heading">
                                <i class="bx bx-calendar-check me-1 text-primary"></i> Sesi Kunjungan Hari Ini
                            </h5>
                            <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Member</th>
                                            <th>Sesi Waktu</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentSchedules as $s)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold small text-heading">
                                                        {{ $s->member->user->name ?? '-' }}</div>
                                                    <small class="text-muted font-monospace"
                                                        style="font-size: 0.7rem;">{{ $s->member->member_code ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-primary font-monospace"
                                                        style="font-size: 0.7rem;">
                                                        {{ $s->timeSlot->name ?? '-' }}
                                                    </span>
                                                    <small class="d-block text-muted"
                                                        style="font-size: 0.68rem;">{{ $s->timeSlot->time_range ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    @if ($s->status === 'attended')
                                                        <span class="badge bg-label-success"
                                                            style="font-size: 0.7rem;">Hadir</span>
                                                    @elseif ($s->status === 'scheduled')
                                                        <span class="badge bg-label-primary"
                                                            style="font-size: 0.7rem;">Terjadwal</span>
                                                    @else
                                                        <span class="badge bg-label-secondary"
                                                            style="font-size: 0.7rem;">{{ ucfirst($s->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted small">
                                                    Belum ada kunjungan terjadwal untuk hari ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
