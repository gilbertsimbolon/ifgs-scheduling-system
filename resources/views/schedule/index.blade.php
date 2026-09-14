@extends('layouts.app')

@section('title', 'Jadwal Kunjungan Member')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-2 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Kunjungan /</span> Jadwal Kunjungan
                </h5>
                <p class="text-muted mb-0">
                    {{ $isMember ? 'Daftar jadwal dan riwayat kehadiran sesi gym Anda.' : 'Monitoring distribusi beban kunjungan member per Time Slot dan eksekusi Algoritma Greedy.' }}
                </p>
            </div>
            @if (!$isMember)
                <div class="d-flex gap-2">
                    <form action="{{ route('schedules.optimize') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="btn btn-warning {{ $pendingReservationsCount > 0 ? 'pulse-warning shadow' : '' }}">
                            <i class="bx bx-brain me-1"></i> Jalankan Optimasi Greedy
                            @if ($pendingReservationsCount > 0)
                                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingReservationsCount }} Pending</span>
                            @endif
                        </button>
                    </form>
                    <a href="{{ route('reservations.index') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Buat Reservasi
                    </a>
                </div>
            @endif
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible" role="alert">
                <i class="bx bx-info-circle me-1"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($isMember)
            <!-- Member Schedule View -->
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold"><i class="bx bx-calendar-star me-1 text-primary"></i> Jadwal & Riwayat Kunjungan Saya</h6>
                    <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-calendar-plus me-1"></i> Reservasi Kunjungan Baru
                    </a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kode Jadwal</th>
                                <th>Tanggal Kunjungan</th>
                                <th>Sesi / Waktu</th>
                                <th>Paket Digunakan</th>
                                <th>Status Kehadiran</th>
                                <th>Catatan / Optimasi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($mySchedules as $index => $sch)
                                <tr>
                                    <td>{{ $mySchedules->firstItem() + $index }}</td>
                                    <td><span class="fw-bold font-monospace text-primary">{{ $sch->schedule_code }}</span></td>
                                    <td>
                                        <span class="fw-semibold">{{ $sch->scheduled_date->format('d M Y') }}</span>
                                        <small class="d-block text-muted">{{ $sch->scheduled_date->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">
                                            <i class="bx bx-time me-1"></i> {{ $sch->timeSlot->name ?? '-' }}
                                        </span>
                                        <small class="d-block text-muted font-monospace">{{ $sch->timeSlot->time_range ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $sch->reservation->membership->product->name ?? 'Membership' }}</span>
                                    </td>
                                    <td>
                                        @if ($sch->status === 'attended')
                                            <span class="badge bg-label-success"><i class="bx bx-check-double me-1"></i> Hadir</span>
                                        @elseif ($sch->status === 'scheduled')
                                            <span class="badge bg-label-primary"><i class="bx bx-calendar me-1"></i> Terjadwal</span>
                                        @elseif ($sch->status === 'no_show')
                                            <span class="badge bg-label-warning"><i class="bx bx-user-x me-1"></i> Tidak Hadir</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i> Batal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted text-wrap" style="max-width: 250px; display: inline-block;">
                                            {{ $sch->notes ?? 'Dialokasikan optimal oleh Algoritma Greedy.' }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bx bx-calendar-x fs-2 mb-2 d-block"></i>
                                        Belum ada jadwal kunjungan untuk akun Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($mySchedules->hasPages())
                    <div class="card-footer d-flex justify-content-end pb-2 pt-3">
                        {{ $mySchedules->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Admin Schedule Monitoring View -->
            <!-- Date Filter Toolbar -->
            <div class="card mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('schedules.index') }}" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1" for="filter_date">Pilih Tanggal Jadwal:</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filter_date" name="date" value="{{ $date }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search-alt"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex gap-1 align-items-end pt-3">
                            <a href="{{ route('schedules.index', ['date' => now()->subDay()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-chevron-left"></i> Kemarin
                            </a>
                            <a href="{{ route('schedules.index', ['date' => now()->format('Y-m-d')]) }}" class="btn {{ $date === now()->format('Y-m-d') ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                Hari Ini
                            </a>
                            <a href="{{ route('schedules.index', ['date' => now()->addDay()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm">
                                Besok <i class="bx bx-chevron-right"></i>
                            </a>
                        </div>
                        <div class="col-md-3 text-md-end pt-3">
                            <span class="badge bg-label-dark font-monospace fs-7 px-3 py-2">
                                <i class="bx bx-calendar me-1"></i> {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Date Metrics Summary -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card card-border-shadow-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-calendar"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $summary['total_scheduled'] }}</h4>
                            </div>
                            <small class="text-muted">Terjadwal Hari Ini</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-border-shadow-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-double"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $summary['total_attended'] }}</h4>
                            </div>
                            <small class="text-muted">Hadir (Selesai Latihan)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-border-shadow-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-user-x"></i></span>
                                </div>
                                <h4 class="mb-0">{{ $summary['total_no_show'] }}</h4>
                            </div>
                            <small class="text-muted">Tidak Hadir (No Show)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card card-border-shadow-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-1">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-info"><i class="bx bx-pie-chart-alt"></i></span>
                                </div>
                                <h4 class="mb-0">
                                    {{ $summary['total_visits'] }}/{{ $summary['total_capacity'] }}
                                </h4>
                            </div>
                            <small class="text-muted">
                                Total Keterisian Gym
                                ({{ $summary['total_capacity'] > 0 ? round(($summary['total_visits'] / $summary['total_capacity']) * 100, 1) : 0 }}%)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notice / Banner for Pending Reservations -->
            @if ($pendingReservationsCount > 0)
                <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-bell fs-3 me-2"></i>
                        <div>
                            <strong>Terdapat {{ $pendingReservationsCount }} Reservasi Belum Terjadwal (Pending)</strong>
                            <div class="small">Jalankan optimasi batch Algoritma Greedy untuk meratakan distribusi member ke Time Slot yang tersedia.</div>
                        </div>
                    </div>
                    <form action="{{ route('schedules.optimize') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">
                        <button type="submit" class="btn btn-sm btn-dark">
                            <i class="bx bx-brain me-1"></i> Optimasi Sekarang
                        </button>
                    </form>
                </div>
            @endif

            <!-- Slot Breakdown Grid Cards -->
            <div class="row g-4">
                @forelse ($slotData as $data)
                    @php
                        $slot = $data['slot'];
                        $pct = $data['percentage'];
                        $progressColor = $pct >= 100 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border {{ $data['is_full'] ? 'border-danger' : 'border-light' }} shadow-sm">
                            <!-- Slot Header -->
                            <div class="card-header pb-2 border-bottom d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title fw-bold mb-1 text-primary">
                                        <i class="bx bx-time-five me-1"></i> {{ $slot->name }}
                                    </h5>
                                    <span class="badge bg-label-dark font-monospace fs-7">
                                        {{ $slot->time_range }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    @if ($data['is_full'])
                                        <span class="badge bg-danger">Kapasitas Penuh</span>
                                    @else
                                        <span class="badge bg-success">Sisa Kuota: {{ $data['remaining'] }}</span>
                                    @endif
                                    <div class="text-muted small mt-1">Batas: {{ $slot->capacity }} org</div>
                                </div>
                            </div>

                            <!-- Occupancy Progress Bar -->
                            <div class="card-body py-3 border-bottom bg-lighter">
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <span class="fw-semibold">Tingkat Kepadatan Sesi (Beban):</span>
                                    <span class="fw-bold">{{ $data['occupied'] }} / {{ $slot->capacity }} Member ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 9px;">
                                    <div class="progress-bar {{ $progressColor }}" role="progressbar"
                                        style="width: {{ min(100, $pct) }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <!-- Member Attendance List in this Slot -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 35px;">#</th>
                                                <th>Member</th>
                                                <th>Paket</th>
                                                <th>Status</th>
                                                <th class="text-center" style="width: 130px;">Aksi Presensi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($data['schedules'] as $idx => $s)
                                                <tr>
                                                    <td class="text-muted small">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold text-heading small">{{ $s->member->user->name ?? '-' }}</div>
                                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $s->member->member_code ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-label-info font-monospace" style="font-size: 0.7rem;">
                                                            {{ $s->reservation->membership->product->name ?? 'Member' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($s->status === 'attended')
                                                            <span class="badge bg-label-success" style="font-size: 0.72rem;">Hadir</span>
                                                        @elseif ($s->status === 'scheduled')
                                                            <span class="badge bg-label-primary" style="font-size: 0.72rem;">Terjadwal</span>
                                                        @elseif ($s->status === 'no_show')
                                                            <span class="badge bg-label-warning" style="font-size: 0.72rem;">No Show</span>
                                                        @else
                                                            <span class="badge bg-label-danger" style="font-size: 0.72rem;">Batal</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                                                                Status
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <form action="{{ route('schedules.update-status', $s) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="attended">
                                                                        <button type="submit" class="dropdown-item text-success small">
                                                                            <i class="bx bx-check-circle me-1"></i> Check-in / Hadir
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('schedules.update-status', $s) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="no_show">
                                                                        <button type="submit" class="dropdown-item text-warning small">
                                                                            <i class="bx bx-user-x me-1"></i> Tidak Hadir (No Show)
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('schedules.update-status', $s) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="scheduled">
                                                                        <button type="submit" class="dropdown-item text-primary small">
                                                                            <i class="bx bx-time me-1"></i> Kembalikan Terjadwal
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form action="{{ route('schedules.update-status', $s) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="cancelled">
                                                                        <button type="submit" class="dropdown-item text-danger small">
                                                                            <i class="bx bx-x me-1"></i> Batalkan Jadwal
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted small">
                                                        Belum ada kunjungan terjadwal di sesi ini.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card p-5 text-center text-muted">
                            <i class="bx bx-calendar-x fs-1 mb-2"></i>
                            <h6>Belum Ada Time Slot Aktif</h6>
                            <p class="mb-3">Silakan buat master data Time Slot terlebih dahulu pada menu Master Data &gt; Time Slot.</p>
                            <div>
                                <a href="{{ route('time-slots.index') }}" class="btn btn-primary">Kelola Time Slot</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
@endsection

