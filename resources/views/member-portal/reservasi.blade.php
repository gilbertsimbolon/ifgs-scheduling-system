@extends('layouts.member')

@section('title', 'Reservasi Kunjungan')

@section('content')
    <!-- Header Halaman -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
                Reservasi Kunjungan
            </h5>
            <small class="text-muted" style="font-size: 0.78rem;">
                Kelola jadwal latihan Anda di IFGS Gym
            </small>
        </div>
        @if ($canMakeReservation)
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBuatReservasi" style="font-size: 0.78rem;">
                <i class="bx bx-plus me-1"></i> Buat Reservasi
            </button>
        @endif
    </div>

    <!-- Alert / Kondisi Khusus Paket Visit 24 Jam -->
    @if ($isDailyVisit)
        <div class="card border-0 bg-info bg-opacity-10 mb-3 shadow-none">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bx bx-info-circle fs-4 text-info flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-info mb-1" style="font-size: 0.88rem;">Paket Visit (24 Jam) Aktif</h6>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                            Anda memiliki paket harian visit. Anda <strong>tidak perlu membuat reservasi</strong>. Anda dapat langsung datang ke gym dan melakukan absensi check-in menggunakan QR code pada hari ini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @elseif (! $activeMembership)
        <div class="card border-0 bg-warning bg-opacity-10 mb-3 shadow-none">
            <div class="card-body p-3 text-center">
                <i class="bx bx-error-circle fs-2 text-warning mb-1"></i>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Memiliki Membership Aktif</h6>
                <p class="small text-muted mb-2" style="font-size: 0.78rem;">
                    Untuk melakukan reservasi kunjungan, silakan berlangganan salah satu paket membership terlebih dahulu.
                </p>
                <a href="{{ route('member.paket-layanan') }}" class="btn btn-primary btn-sm rounded-pill px-3" style="font-size: 0.78rem;">
                    <i class="bx bx-package me-1"></i> Lihat Paket Layanan
                </a>
            </div>
        </div>
    @endif

    <!-- Tab Section: Reservasi Aktif & Terjadwal -->
    <div class="card mb-3">
        <div class="card-header py-2 px-3 bg-white border-bottom">
            <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-calendar-check text-primary"></i> Reservasi Saya
            </div>
        </div>
        <div class="card-body p-3">
            @if ($activeReservations->isNotEmpty() || $upcomingSchedules->isNotEmpty())
                <!-- Gabungan jadwal terkonfirmasi & reservasi aktif -->
                <div class="d-flex flex-column gap-2">
                    <!-- Terjadwal (Schedules) -->
                    @foreach ($upcomingSchedules as $sched)
                        <div class="p-3 border rounded-3 bg-white hover-shadow position-relative">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-success text-white rounded p-2 text-center" style="min-width: 44px;">
                                        <span class="d-block fw-bold fs-6 lh-1">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->format('d') }}
                                        </span>
                                        <small class="d-block text-uppercase" style="font-size: 0.65rem;">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->translatedFormat('M') }}
                                        </small>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->translatedFormat('l, d F Y') }}
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            <i class="bx bx-time-five me-1 text-primary"></i>
                                            {{ $sched->timeSlot?->formatted_time ?? 'Waktu disesuaikan' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                    Dikonfirmasi
                                </span>
                            </div>

                            @if ($sched->timeSlot)
                                <div class="small text-muted bg-light p-2 rounded-2 mb-2" style="font-size: 0.72rem;">
                                    <span class="fw-semibold text-dark">{{ $sched->timeSlot->name }}</span> &bull; Kuota: {{ $sched->timeSlot->quota }} orang
                                </div>
                            @endif

                            @if ($sched->reservation && in_array($sched->reservation->status, [\App\Models\Reservation::STATUS_PENDING, \App\Models\Reservation::STATUS_SCHEDULED]))
                                <div class="text-end">
                                    <form action="{{ route('reservations.cancel', $sched->reservation->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bx bx-x me-1"></i> Batalkan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Reservasi yang belum dibuatkan schedule langsung (Pending) -->
                    @foreach ($activeReservations as $res)
                        @if (! $upcomingSchedules->contains('reservation_id', $res->id))
                            <div class="p-3 border rounded-3 bg-white hover-shadow position-relative">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-warning text-white rounded p-2 text-center" style="min-width: 44px;">
                                            <span class="d-block fw-bold fs-6 lh-1">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->format('d') }}
                                            </span>
                                            <small class="d-block text-uppercase" style="font-size: 0.65rem;">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->translatedFormat('M') }}
                                            </small>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->translatedFormat('l, d F Y') }}
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                <i class="bx bx-time-five me-1 text-warning"></i>
                                                {{ $res->timeSlot?->formatted_time ?? 'Slot Otomatis (Greedy)' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        Menunggu Jadwal
                                    </span>
                                </div>

                                @if ($res->notes)
                                    <p class="text-muted small mb-2 bg-light p-2 rounded-2" style="font-size: 0.72rem;">
                                        <i class="bx bx-note me-1"></i> {{ $res->notes }}
                                    </p>
                                @endif

                                <div class="text-end">
                                    <form action="{{ route('reservations.cancel', $res->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?');" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bx bx-x me-1"></i> Batalkan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bx bx-calendar-minus fs-1 text-secondary opacity-50 mb-2"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Reservasi Aktif</h6>
                    <p class="text-muted small mb-3" style="font-size: 0.75rem;">
                        Anda belum memiliki jadwal reservasi latihan yang akan datang.
                    </p>
                    @if ($canMakeReservation)
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBuatReservasi" style="font-size: 0.78rem;">
                            <i class="bx bx-plus me-1"></i> Buat Reservasi Sekarang
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Informasi Ketentuan Reservasi Gym -->
    <div class="card mb-3">
        <div class="card-header py-2 px-3 bg-white">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-info-circle text-primary"></i> Ketentuan Reservasi
            </h6>
        </div>
        <div class="card-body p-3">
            <ul class="text-muted small ps-3 mb-0" style="font-size: 0.75rem; line-height: 1.5;">
                <li>Gym buka setiap hari <strong>Senin s.d. Sabtu</strong> (Minggu libur).</li>
                <li>Pemesanan slot disesuaikan dengan kuota kapasitas gym untuk menjaga kenyamanan latihan.</li>
                <li>Sistem penjadwalan menggunakan algoritma otomatis untuk mendistribusikan member secara merata.</li>
                <li>Tunjukkan kartu digital atau QR Code akun Anda saat tiba di meja resepsionis/kasir gym.</li>
            </ul>
        </div>
    </div>

    <!-- Modal Buat Reservasi Kunjungan -->
    @if ($canMakeReservation)
        <div class="modal fade" id="modalBuatReservasi" tabindex="-1" aria-labelledby="modalBuatReservasiLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom py-2 px-3">
                        <h6 class="modal-title fw-bold text-dark fs-6" id="modalBuatReservasiLabel">
                            <i class="bx bx-calendar-plus text-primary me-1"></i> Form Reservasi Latihan
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('reservations.store') }}" method="POST">
                        @csrf
                        <div class="modal-body p-3">
                            <div class="mb-3">
                                <label for="visit_date" class="form-label small fw-bold text-dark">Tanggal Kunjungan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="visit_date" name="visit_date"
                                    min="{{ date('Y-m-d') }}"
                                    value="{{ date('Y-m-d') }}" required>
                                <small class="text-muted" style="font-size: 0.7rem;">Pilih tanggal latihan (Senin - Sabtu).</small>
                            </div>

                            <div class="mb-3">
                                <label for="time_slot_id" class="form-label small fw-bold text-dark">Pilihan Jam / Time Slot</label>
                                <select class="form-select form-select-sm" id="time_slot_id" name="time_slot_id">
                                    <option value="">Otomatis Ditentukan Sistem (Rekomendasi)</option>
                                    @foreach ($operationalSlots as $slot)
                                        <option value="{{ $slot->id }}">
                                            {{ $slot->name }} ({{ $slot->formatted_time }}) - Kuota: {{ $slot->quota }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" style="font-size: 0.7rem;">Jika kosong, sistem akan mengalokasikan slot waktu paling optimal.</small>
                            </div>

                            <div class="mb-2">
                                <label for="notes" class="form-label small fw-bold text-dark">Catatan Tambahan (Opsional)</label>
                                <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="Contoh: Fokus leg day, perlu bantuan instruktur, dll."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-2 px-3">
                            <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                <i class="bx bx-check me-1"></i> Konfirmasi Reservasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

