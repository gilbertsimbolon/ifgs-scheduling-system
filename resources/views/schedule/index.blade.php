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
                                            <span class="badge bg-label-success"><i class="bx bx-check-double me-1"></i>
                                                Hadir</span>
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
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bx bx-calendar-x fs-2 mb-2 d-block"></i>
                                        Belum ada jadwal kehadiran untuk akun Anda.
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
            <!-- 2 Card Sesi Layanan Dinamis -->
            <div class="row g-4 mt-1">
                @forelse ($slotData as $data)
                    @php
                        $slot = $data['slot'];
                        $pct = $data['percentage'];
                        $progressColor = $pct >= 100 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border {{ $data['is_full'] ? 'border-danger' : 'border-light' }} shadow-sm">
                            <!-- Slot Header -->
                            <div class="card-header py-3 border-bottom">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="d-flex align-items-center text-primary">
                                        <i class="bx bx-time-five me-2 fs-5 d-flex align-items-center" style="line-height: 1;"></i>
                                        <h5 class="card-title fw-bold mb-0 text-primary" style="line-height: 1.2;">
                                            {{ $slot->name }}
                                        </h5>
                                    </div>
                                    <span class="badge bg-label-secondary d-inline-flex align-items-center" style="font-size: 0.8rem; font-weight: 500; height: 26px; line-height: 1;">
                                        {{ $slot->time_range }} WITA
                                    </span>
                                </div>
                            </div>

                            <!-- Occupancy Progress Bar -->
                            <div class="card-body py-3 border-bottom bg-lighter">
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <span class="fw-semibold text-dark">Tingkat Kepadatan Sesi:</span>
                                    <span class="fw-bold text-dark">{{ $data['occupied'] }} / {{ $slot->capacity }} Member
                                        ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 9px;">
                                    <div class="progress-bar {{ $progressColor }}" role="progressbar"
                                        style="width: {{ min(100, $pct) }}%;" aria-valuenow="{{ $pct }}"
                                        aria-valuemin="0" aria-valuemax="100">
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
                                                <th>MEMBER</th>
                                                <th>PAKET</th>
                                                <th>STATUS</th>
                                                <th class="text-center" style="width: 130px;">AKSI PRESENSI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($data['schedules'] as $idx => $s)
                                                <tr>
                                                    <td class="text-muted small">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <div class="fw-semibold text-dark small">
                                                            {{ $s->member->user->name ?? '-' }}</div>
                                                        <small class="text-muted"
                                                            style="font-size: 0.75rem;">{{ $s->member->member_code ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="text-dark small">
                                                            {{ $s->reservation->membership->product->name ?? 'Membership' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($s->status === 'attended')
                                                            <span class="badge bg-label-success"
                                                                style="font-size: 0.72rem;">Hadir</span>
                                                        @elseif ($s->status === 'scheduled')
                                                            <span class="badge bg-label-primary"
                                                                style="font-size: 0.72rem;">Terjadwal</span>
                                                        @elseif ($s->status === 'no_show')
                                                            <span class="badge bg-label-warning"
                                                                style="font-size: 0.72rem;">No Show</span>
                                                        @else
                                                            <span class="badge bg-label-danger"
                                                                style="font-size: 0.72rem;">Batal</span>
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
                                                                            Check-in / Hadir
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
                            <h6>Belum Ada Sesi / Jadwal Layanan Aktif</h6>
                            <p class="mb-3">Silakan kelola jadwal operasional pada menu Operasional &gt; Jadwal
                                Operasional.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
@endsection
