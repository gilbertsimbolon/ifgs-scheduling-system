@extends('layouts.app')

@section('title', 'Kunjungan')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible my-3" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible my-3" role="alert">
                <i class="bx bx-info-circle me-1"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible my-3" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($isMember)
            <!-- Tampilan Kunjungan Member Personal -->
            <div class="card mt-3">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold"><i class="bx bx-calendar-check me-1 text-primary"></i> Jadwal &
                        Riwayat Kunjungan Saya</h6>
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
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($mySchedules as $index => $sch)
                                <tr>
                                    <td>{{ $mySchedules->firstItem() + $index }}</td>
                                    <td><span class="fw-bold text-primary">{{ $sch->schedule_code }}</span></td>
                                    <td>
                                        <span class="fw-semibold">{{ $sch->scheduled_date->format('d M Y') }}</span>
                                        <small
                                            class="d-block text-muted">{{ $sch->scheduled_date->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $sch->timeSlot->name ?? '-' }}</span>
                                        <small class="d-block text-muted">{{ $sch->timeSlot->time_range ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="text-dark">{{ $sch->reservation->membership->product->name ?? 'Membership' }}</span>
                                    </td>
                                    <td>
                                        @if ($sch->status === 'attended')
                                            <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>
                                                Check-in</span>
                                        @elseif ($sch->status === 'scheduled')
                                            <span class="badge bg-label-primary"><i class="bx bx-calendar me-1"></i>
                                                Terjadwal</span>
                                        @elseif ($sch->status === 'no_show')
                                            <span class="badge bg-label-warning"><i class="bx bx-user-x me-1"></i> Tidak
                                                Hadir</span>
                                        @else
                                            <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i> Batal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted text-wrap"
                                            style="max-width: 250px; display: inline-block;">
                                            {{ $sch->notes ?? '-' }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                            <i class="bx bx-calendar-x text-muted mb-2"
                                                style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h6 class="text-secondary fw-semibold mb-1">Belum Ada Jadwal Kehadiran</h6>
                                            <p class="text-muted small mb-0">Belum ada jadwal kehadiran untuk akun Anda.</p>
                                        </div>
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
            <!-- Filter Tanggal Kunjungan Operasional -->
            <div class="card mb-3">
                <div class="card-body py-2 px-3">
                    <form method="GET" action="{{ route('schedules.index') }}"
                        class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-calendar text-primary fs-4"></i>
                            <div>
                                <span class="fw-bold text-dark d-block" style="font-size: 0.9rem;">
                                    Jadwal Kunjungan: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                </span>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($date)->isToday() ? '(Hari Ini)' : (\Carbon\Carbon::parse($date)->isTomorrow() ? '(Besok)' : '') }}
                                </small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="filterDate" class="form-label mb-0 small text-muted">Pilih Tanggal:</label>
                            <input type="date" id="filterDate" name="date" class="form-control form-control-sm"
                                value="{{ $date }}" onchange="this.form.submit()" style="width: 160px;">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bx bx-search-alt me-1"></i> Tampilkan
                            </button>
                            @if (!\Carbon\Carbon::parse($date)->isToday())
                                <a href="{{ route('schedules.index') }}" class="btn btn-sm btn-outline-secondary">
                                    Hari Ini
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Nav Tabs Sesi Layanan Dinamis Full Width -->
            @if (count($slotData) > 0)
                <div class="nav-align-top mt-2">
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($slotData as $index => $data)
                            @php
                                $slot = $data['slot'];
                                $pct = $data['percentage'];
                                $badgeClass = $data['is_full']
                                    ? 'bg-danger'
                                    : ($pct >= 70
                                        ? 'bg-warning'
                                        : 'bg-primary');
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                    class="nav-link py-3 px-4 fw-bold {{ $index === 0 ? 'active' : '' }}" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#navs-slot-{{ $slot->id }}"
                                    aria-controls="navs-slot-{{ $slot->id }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <i
                                        class="bx {{ $slot->category === 'aerobic_zumba' ? 'bx-run' : 'bx-dumbbell' }} me-2 text-primary fs-5"></i>
                                    <span>{{ $slot->name }}</span>
                                    <span class="badge bg-label-secondary ms-2 fw-normal" style="font-size: 0.8rem;">
                                        {{ $slot->time_range }} WITA
                                    </span>
                                    <span class="badge rounded-pill {{ $badgeClass }} ms-2" style="font-size: 0.75rem;">
                                        {{ $data['occupied'] }}/{{ $slot->capacity }}
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content p-0 shadow-sm border-0">
                        @foreach ($slotData as $index => $data)
                            @php
                                $slot = $data['slot'];
                                $pct = $data['percentage'];
                                $progressColor = $pct >= 100 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                id="navs-slot-{{ $slot->id }}" role="tabpanel">
                                <!-- Tingkat Kepadatan Sesi Full Width Bar -->
                                <div class="px-4 py-3 border-bottom bg-lighter">
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-1 small flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold text-dark">
                                                <i class="bx bx-tachometer me-1 text-primary"></i> Tingkat Kepadatan Sesi:
                                            </span>
                                            <span class="badge bg-label-secondary">{{ $slot->days }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark">
                                                {{ $data['occupied'] }} / {{ $slot->capacity }} Member
                                                ({{ $pct }}%)
                                            </span>
                                            <span class="mx-1 text-muted">&bull;</span>
                                            <span
                                                class="fw-semibold {{ $data['is_full'] ? 'text-danger' : 'text-success' }}">
                                                Sisa {{ max(0, $slot->capacity - $data['occupied']) }} Slot
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar {{ $progressColor }}" role="progressbar"
                                            style="width: {{ min(100, $pct) }}%;" aria-valuenow="{{ $pct }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabel Kunjungan Full Width -->
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover align-middle mb-0 w-100">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>MEMBER</th>
                                                <th>PAKET</th>
                                                <th>STATUS</th>
                                                <th class="text-center" style="width: 150px;">AKSI PRESENSI</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            @forelse ($data['schedules'] as $idx => $s)
                                                <tr>
                                                    <td class="text-muted">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold text-dark">
                                                            {{ $s->member->user->name ?? '-' }}</div>
                                                        <small class="text-muted"
                                                            style="font-size: 0.75rem;">{{ $s->member->member_code ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="text-dark">
                                                            {{ $s->reservation->membership->product->name ?? 'Membership' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($s->status === 'attended')
                                                            <span class="badge bg-label-success"><i
                                                                    class="bx bx-check-circle me-1"></i> Check-in</span>
                                                        @elseif ($s->status === 'scheduled')
                                                            <span class="badge bg-label-primary"><i
                                                                    class="bx bx-calendar me-1"></i> Terjadwal</span>
                                                        @elseif ($s->status === 'no_show')
                                                            <span class="badge bg-label-warning"><i
                                                                    class="bx bx-user-x me-1"></i> No Show</span>
                                                        @else
                                                            <span class="badge bg-label-danger"><i
                                                                    class="bx bx-x me-1"></i> Batal</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <button
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2"
                                                                type="button" data-bs-toggle="dropdown"
                                                                aria-expanded="false" style="font-size: 0.75rem;">
                                                                Status
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <form
                                                                        action="{{ route('schedules.update-status', $s) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status"
                                                                            value="attended">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-success small">
                                                                            <i class="bx bx-check-circle me-1"></i>
                                                                            Check-in
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form
                                                                        action="{{ route('schedules.update-status', $s) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status"
                                                                            value="no_show">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-warning small">
                                                                            <i class="bx bx-user-x me-1"></i> Tidak Hadir
                                                                            (No Show)
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form
                                                                        action="{{ route('schedules.update-status', $s) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status"
                                                                            value="scheduled">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-primary small">
                                                                            <i class="bx bx-time me-1"></i> Kembalikan
                                                                            Terjadwal
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <form
                                                                        action="{{ route('schedules.update-status', $s) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status"
                                                                            value="cancelled">
                                                                        <button type="submit"
                                                                            class="dropdown-item text-danger small">
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
                                                    <td colspan="5" class="text-center py-5">
                                                        <div
                                                            class="d-flex flex-column align-items-center justify-content-center py-4">
                                                            <i class="bx bx-calendar-x text-muted mb-2"
                                                                style="font-size: 3rem; opacity: 0.5;"></i>
                                                            <h6 class="text-secondary fw-semibold mb-1">Belum Ada Kunjungan Terjadwal</h6>
                                                            <p class="text-muted small mb-0">Belum ada kunjungan terjadwal di sesi ini.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card p-5 text-center text-muted mt-2">
                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                        <i class="bx bx-calendar-x fs-1 mb-2 text-secondary"></i>
                        <h6 class="fw-bold text-secondary mb-1">Belum Ada Sesi / Jadwal Layanan Aktif</h6>
                        <p class="text-muted small mb-0">Silakan kelola jadwal operasional pada menu Operasional &gt;
                            Jadwal Operasional.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
