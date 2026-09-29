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
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                data-bs-target="#modalBuatReservasi" style="font-size: 0.78rem;">
                <i class="bx bx-plus me-1"></i> Buat Reservasi
            </button>
        @endif
    </div>

    <!-- Alert Jika Belum Memiliki Membership Aktif -->
    @if (!$activeMembership)
        <div class="card border-0 bg-light mb-3 shadow-none border">
            <div class="card-body p-3 text-center">
                <i class="bx bx-error-circle fs-2 text-warning mb-1"></i>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Memiliki Membership Aktif</h6>
                <p class="small text-muted mb-2" style="font-size: 0.78rem;">
                    Untuk melakukan reservasi kunjungan, silakan berlangganan salah satu paket membership terlebih dahulu.
                </p>
                <a href="{{ route('member.paket-layanan') }}" class="btn btn-primary btn-sm rounded-pill px-3"
                    style="font-size: 0.78rem;">
                    <i class="bx bx-package me-1"></i> Lihat Paket Layanan
                </a>
            </div>
        </div>
    @endif

    <!-- Section: Reservasi Aktif & Terjadwal -->
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div class="card-header py-2 px-3 bg-white border-bottom rounded-top-3">
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
                        <div class="p-3 border rounded-3 bg-white shadow-xs position-relative hover-shadow">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 170px;">
                                    <div class="rounded-3 p-2 text-center shadow-xs flex-shrink-0 border"
                                        style="min-width: 50px; background-color: #f8fafc; border-color: #e2e8f0;">
                                        <span class="d-block fw-bold fs-6 lh-1" style="color: #1e293b;">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->format('d') }}
                                        </span>
                                        <small class="d-block text-uppercase fw-semibold"
                                            style="font-size: 0.65rem; color: #64748b;">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->translatedFormat('M') }}
                                        </small>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark small text-truncate">
                                            {{ \Carbon\Carbon::parse($sched->scheduled_date)->translatedFormat('l, d F Y') }}
                                        </div>
                                        <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                            <i class="bx bx-time-five me-1 text-primary"></i>
                                            {{ $sched->timeSlot?->time_range ?? '08:00 - 20:00' }}
                                        </div>
                                    </div>
                                </div>
                                @if ($sched->status === \App\Models\Schedule::STATUS_ATTENDED)
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                        style="font-size: 0.7rem;">
                                        <i class="bx bx-check-circle me-1"></i>Check-in Hadir
                                    </span>
                                @elseif ($sched->status === \App\Models\Schedule::STATUS_NO_SHOW)
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                        style="font-size: 0.7rem;">
                                        <i class="bx bx-user-x me-1"></i>Tidak Hadir
                                    </span>
                                @elseif ($sched->status === \App\Models\Schedule::STATUS_CANCELLED)
                                    <span
                                        class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2 py-1 flex-shrink-0"
                                        style="font-size: 0.7rem;">
                                        <i class="bx bx-x me-1"></i>Dibatalkan
                                    </span>
                                @else
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                        style="font-size: 0.7rem;">
                                        <i class="bx bx-check-circle me-1"></i>Dikonfirmasi
                                    </span>
                                @endif
                            </div>

                            @if ($sched->timeSlot)
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 rounded-2 mb-2 border"
                                    style="font-size: 0.74rem; background-color: #f8fafc; border-color: #e2e8f0;">
                                    <span class="fw-semibold text-dark"><i
                                            class="bx bx-dumbbell me-1 text-primary"></i>{{ $sched->timeSlot->name }}</span>
                                    <span
                                        class="badge bg-white text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1">Kapasitas:
                                        {{ $sched->timeSlot->effective_reservation_quota }} Orang</span>
                                </div>
                            @endif

                            @if (
                                $sched->reservation &&
                                    in_array($sched->reservation->status, [
                                        \App\Models\Reservation::STATUS_PENDING,
                                        \App\Models\Reservation::STATUS_SCHEDULED,
                                    ]))
                                <div class="text-end pt-1">
                                    <form action="{{ route('reservations.cancel', $sched->reservation->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="redirect_to" value="{{ route('member.reservasi') }}">
                                        <button type="button" onclick="confirmCancelReservation(this)"
                                            class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill text-danger border-secondary border-opacity-25"
                                            style="font-size: 0.72rem;">
                                            <i class="bx bx-x me-1"></i> Batalkan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Reservasi yang belum dibuatkan schedule langsung (Pending) -->
                    @foreach ($activeReservations as $res)
                        @if (!$upcomingSchedules->contains('reservation_id', $res->id))
                            <div class="p-3 border rounded-3 bg-white shadow-xs position-relative hover-shadow">
                                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 170px;">
                                        <div class="rounded-3 p-2 text-center shadow-xs flex-shrink-0 border"
                                            style="min-width: 50px; background-color: #f8fafc; border-color: #e2e8f0;">
                                            <span class="d-block fw-bold fs-6 lh-1" style="color: #1e293b;">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->format('d') }}
                                            </span>
                                            <small class="d-block text-uppercase fw-semibold"
                                                style="font-size: 0.65rem; color: #64748b;">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->translatedFormat('M') }}
                                            </small>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark small text-truncate">
                                                {{ \Carbon\Carbon::parse($res->visit_date)->translatedFormat('l, d F Y') }}
                                            </div>
                                            <div class="text-muted small fw-semibold" style="font-size: 0.75rem;">
                                                <i class="bx bx-time-five me-1 text-warning"></i>
                                                {{ $res->timeSlot?->time_range ?? 'Slot Otomatis (Greedy)' }}
                                            </div>
                                        </div>
                                    </div>
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                        style="font-size: 0.7rem;">
                                        <i class="bx bx-time me-1"></i>Menunggu Jadwal
                                    </span>
                                </div>

                                @if ($res->notes)
                                    <div class="p-2 rounded-2 mb-2 border border-secondary border-opacity-20 bg-light text-dark small"
                                        style="font-size: 0.72rem;">
                                        <i class="bx bx-note me-1 text-muted"></i> {{ $res->notes }}
                                    </div>
                                @endif

                                <div class="text-end pt-1">
                                    <form action="{{ route('reservations.cancel', $res->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="redirect_to" value="{{ route('member.reservasi') }}">
                                        <button type="button" onclick="confirmCancelReservation(this)"
                                            class="btn btn-outline-secondary btn-sm py-1 px-3 rounded-pill text-danger border-secondary border-opacity-25"
                                            style="font-size: 0.72rem;">
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
                    <div class="avatar avatar-md bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="bx bx-calendar-plus fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">Belum Ada Reservasi Aktif</h6>
                    <p class="text-muted small mb-3" style="font-size: 0.75rem;">
                        Anda belum memiliki jadwal reservasi latihan yang akan datang.
                    </p>
                    @if ($canMakeReservation)
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#modalBuatReservasi" style="font-size: 0.78rem;">
                            <i class="bx bx-plus me-1"></i> Buat Reservasi Sekarang
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Informasi Ketentuan Reservasi Gym -->
    <div class="card mb-3 border-0 border-start border-4 border-primary shadow-sm rounded-3">
        <div class="card-header py-2 px-3 bg-white border-bottom">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-info-circle text-primary"></i> Ketentuan Reservasi
            </h6>
        </div>
        <div class="card-body p-3">
            <ul class="text-dark small ps-3 mb-0" style="font-size: 0.78rem; line-height: 1.6;">
                <li>Gym buka setiap hari <strong>Senin s.d. Sabtu</strong> (Minggu libur).</li>
                <li>Pemesanan slot disesuaikan dengan kuota kapasitas gym untuk menjaga kenyamanan latihan.</li>
                <li>Sistem penjadwalan menggunakan algoritma otomatis untuk mendistribusikan member secara merata.</li>
                <li>Tunjukkan kartu digital atau QR Code akun Anda saat tiba di meja resepsionis/kasir gym.</li>
            </ul>
        </div>
    </div>

    <!-- Modal Reservasi Latihan (Multi-Step Panel) -->
    @if ($canMakeReservation)
        <div class="modal fade" id="modalBuatReservasi" tabindex="-1" aria-labelledby="modalBuatReservasiLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content border-0 shadow">
                    <!-- Modal Header (Tanpa paragraf deskripsi) -->
                    <div class="modal-header border-bottom py-2 px-3 bg-white">
                        <h6 class="modal-title fw-bold text-dark fs-6" id="modalBuatReservasiLabel">
                            <i class="bx bx-calendar-plus text-primary me-1"></i> Reservasi Latihan
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('reservations.store') }}" method="POST" id="formBuatReservasi">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('member.reservasi') }}">

                        <div class="modal-body p-3">
                            <!-- Indikator Tahap (2 Langkah) -->
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-3 pb-2 border-bottom">
                                <span class="badge rounded-pill px-3 py-1 bg-primary text-white shadow-xs" id="stepBadge1"
                                    style="font-size: 0.72rem;">
                                    <i class="bx bx-calendar-event me-1"></i> 1. Paket & Tanggal
                                </span>
                                <i class="bx bx-chevron-right text-muted opacity-50" style="font-size: 0.85rem;"></i>
                                <span class="badge rounded-pill px-3 py-1 bg-white text-dark border" id="stepBadge2"
                                    style="font-size: 0.72rem;">
                                    <i class="bx bx-check-shield me-1"></i> 2. Konfirmasi & Panduan
                                </span>
                            </div>

                            <!-- PANEL 1: PAKET LAYANAN & TANGGAL KUNJUNGAN -->
                            <div id="stepPanel1">
                                <!-- Paket Layanan -->
                                <div class="mb-3">
                                    <label
                                        class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-2">
                                        <span><i class="bx bx-package text-primary me-1"></i> Paket Layanan <span
                                                class="text-danger">*</span></span>
                                    </label>

                                    <div class="row g-2" id="packageCardsRow">
                                        @foreach ($operationalSlots as $slot)
                                            @php
                                                $isSubscribed = in_array($slot->category, $subscribedCategories);
                                                $isDefaultChecked = $slot->id == $defaultSelectedSlotId;
                                            @endphp
                                            <div class="col-12 col-md-6">
                                                <div class="card h-100 p-2 position-relative package-slot-card {{ $isSubscribed ? ($isDefaultChecked ? 'active-package-card' : 'border bg-white') : 'border border-secondary border-opacity-25 bg-white not-subscribed-card' }}"
                                                    id="card_slot_{{ $slot->id }}"
                                                    data-slot-id="{{ $slot->id }}"
                                                    data-category="{{ $slot->category }}"
                                                    data-quota="{{ $slot->effective_reservation_quota }}"
                                                    data-subscribed="{{ $isSubscribed ? '1' : '0' }}"
                                                    style="{{ $isSubscribed ? 'cursor: pointer;' : 'cursor: not-allowed;' }} transition: all 0.2s;">
                                                    <div
                                                        class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <input class="form-check-input package-slot-radio m-0"
                                                                type="radio" name="time_slot_id"
                                                                id="radio_slot_{{ $slot->id }}"
                                                                value="{{ $slot->id }}"
                                                                {{ $isSubscribed ? ($isDefaultChecked ? 'checked' : '') : 'disabled' }}
                                                                style="width: 1.15em; height: 1.15em; cursor: {{ $isSubscribed ? 'pointer' : 'not-allowed' }};">
                                                            <div>
                                                                <label
                                                                    class="form-check-label fw-bold text-dark mb-0 d-block"
                                                                    for="radio_slot_{{ $slot->id }}"
                                                                    style="font-size: 0.85rem; cursor: {{ $isSubscribed ? 'pointer' : 'not-allowed' }};">
                                                                    {{ $slot->name }}
                                                                </label>
                                                                <div class="text-muted small fw-semibold"
                                                                    style="font-size: 0.72rem;">
                                                                    <i
                                                                        class="bx bx-time-five me-1 text-primary"></i>{{ $slot->time_range }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if ($isSubscribed)
                                                            <span
                                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                                                style="font-size: 0.68rem;">
                                                                <i class="bx bx-check-circle me-1"></i>Berlangganan
                                                            </span>
                                                        @else
                                                            <span
                                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 flex-shrink-0"
                                                                style="font-size: 0.68rem;">
                                                                <i class="bx bx-lock-alt me-1"></i>Belum Berlangganan
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Slot Info Footer -->
                                                    <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center"
                                                        style="font-size: 0.72rem;">
                                                        <span class="text-muted">
                                                            <i class="bx bx-calendar me-1"></i>{{ $slot->days }}
                                                        </span>
                                                        @if ($isSubscribed)
                                                            <span
                                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill"
                                                                style="font-size: 0.66rem;">
                                                                Kapasitas: {{ $slot->effective_reservation_quota }} Slot
                                                            </span>
                                                        @else
                                                            <a href="{{ route('member.paket-layanan') }}"
                                                                class="text-primary text-decoration-none fw-semibold">
                                                                Beli Paket <i class="bx bx-chevron-right"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Tanggal Kunjungan -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="bx bx-calendar text-primary me-1"></i> Tanggal Kunjungan <span
                                                class="text-danger">*</span>
                                        </label>
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2"
                                            style="font-size: 0.68rem;" id="badgeSelectedDate">
                                            {{ $availableDays[0]['day_name'] ?? '' }},
                                            {{ $availableDays[0]['day_number'] ?? '' }}
                                            {{ $availableDays[0]['month_name'] ?? '' }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            <i class="bx bx-time-five me-1 text-primary"></i>Pilihan hari disesuaikan
                                            dengan
                                            sisa durasi paket Anda.
                                        </small>
                                        <span
                                            class="badge bg-light text-muted border border-secondary border-opacity-25 rounded-pill px-2 py-0"
                                            style="font-size: 0.65rem;" id="badgeDaysCount">
                                            {{ count($availableDays) }} Hari Tersedia
                                        </span>
                                    </div>

                                    <!-- Grid Kartu Hari Operasional (Dinamis Sesuai Durasi) -->
                                    <div class="days-grid-10 mb-1 {{ count($availableDays) === 1 ? 'has-single-card' : '' }}"
                                        id="operationalDaysGrid">
                                        @foreach ($availableDays as $index => $day)
                                            @php
                                                $isInitialActive = $index === 0;
                                            @endphp
                                            <button type="button"
                                                class="btn day-pill-btn text-center rounded-3 border d-flex flex-column align-items-center justify-content-between {{ $isInitialActive ? 'active-day-pill' : 'bg-white' }}"
                                                data-date="{{ $day['date'] }}"
                                                data-formatted="{{ $day['day_name'] }}, {{ $day['day_number'] }} {{ $day['month_name'] }}"
                                                data-remaining="{{ $day['remaining'] }}"
                                                data-quota="{{ $day['quota'] }}"
                                                data-formatted-slot="{{ $day['formatted'] }}"
                                                style="cursor: pointer; width: 100%;">
                                                <span
                                                    class="small fw-semibold day-label {{ $isInitialActive ? 'text-white' : 'text-muted' }}"
                                                    style="font-size: 0.65rem; line-height: 1.1;">
                                                    {{ $day['label'] }}
                                                </span>
                                                <span
                                                    class="fw-bold fs-6 my-1 day-number {{ $isInitialActive ? 'text-white' : 'text-dark' }}"
                                                    style="line-height: 1;">
                                                    {{ $day['day_number'] }}
                                                </span>
                                                <span
                                                    class="day-month {{ $isInitialActive ? 'text-white' : 'text-muted' }}"
                                                    style="font-size: 0.6rem; line-height: 1;">
                                                    {{ $day['month_name'] }}
                                                </span>
                                                <span
                                                    class="badge rounded-pill day-slot-badge mt-1 py-1 px-1 w-100 text-truncate {{ $isInitialActive ? 'bg-white text-primary fw-bold' : ($day['remaining'] <= 0 ? 'bg-secondary text-white fw-semibold' : 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fw-semibold') }}"
                                                    style="font-size: 0.62rem;">
                                                    {{ $day['remaining'] <= 0 ? 'Penuh' : $day['formatted'] }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>

                                    <input type="hidden" name="visit_date" id="visit_date"
                                        value="{{ $availableDays[0]['date'] ?? date('Y-m-d') }}" required>
                                </div>

                                <!-- Catatan Tambahan (Opsional) -->
                                <div class="mb-1">
                                    <label for="notes"
                                        class="form-label small fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                        <span><i class="bx bx-note text-primary me-1"></i> Catatan Tambahan</span>
                                        <span class="text-muted fw-normal" style="font-size: 0.7rem;">Opsional</span>
                                    </label>
                                    <textarea class="form-control form-control-sm rounded-3" id="notes" name="notes" rows="2"
                                        placeholder="Contoh: Fokus latihan kardio, datang bersama rekan, ada riwayat cedera, dll."
                                        style="font-size: 0.78rem;"></textarea>
                                </div>
                            </div>

                            <!-- PANEL 2: INFORMASI & KONFIRMASI -->
                            <div id="stepPanel2" class="d-none">
                                <!-- Ringkasan Pemesanan -->
                                <div
                                    class="card border border-primary border-opacity-20 rounded-3 p-3 bg-white shadow-sm mb-3">
                                    <h6 class="fw-bold text-dark mb-2 pb-2 border-bottom d-flex align-items-center gap-2"
                                        style="font-size: 0.85rem;">
                                        <i class="bx bx-receipt text-primary"></i> Ringkasan Kunjungan
                                    </h6>
                                    <div class="row g-2" style="font-size: 0.78rem;">
                                        <div
                                            class="col-12 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                            <span class="text-muted"><i class="bx bx-package text-primary me-1"></i> Paket
                                                Layanan:</span>
                                            <span class="fw-bold text-dark text-end">
                                                <span id="summaryPackage">-</span>
                                                <span id="summaryTime" class="text-muted small d-block"></span>
                                            </span>
                                        </div>
                                        <div
                                            class="col-12 py-2 border-bottom d-flex align-items-center justify-content-between">
                                            <span class="text-muted"><i class="bx bx-calendar text-primary me-1"></i>
                                                Tanggal Kunjungan:</span>
                                            <span class="fw-bold text-primary text-end" id="summaryDate">-</span>
                                        </div>
                                        <div
                                            class="col-12 py-2 border-bottom d-flex align-items-center justify-content-between">
                                            <span class="text-muted"><i class="bx bx-group text-primary me-1"></i> Sisa
                                                Kuota Slot:</span>
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold"
                                                id="summaryQuota">-</span>
                                        </div>
                                        <div class="col-12 pt-2 d-flex align-items-start justify-content-between">
                                            <span class="text-muted"><i class="bx bx-note text-primary me-1"></i>
                                                Catatan:</span>
                                            <span class="text-dark small text-end text-break" style="max-width: 60%;"
                                                id="summaryNotes">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Panduan Penting Kunjungan -->
                                <div class="card border border-primary border-opacity-20 bg-light rounded-3 p-3 mb-1">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                                        <i class="bx bx-info-circle fs-5"></i>
                                        <h6 class="fw-bold text-primary mb-0" style="font-size: 0.85rem;">Panduan
                                            Kunjungan
                                            & Absensi</h6>
                                    </div>
                                    <ul class="text-dark small ps-3 mb-0" style="font-size: 0.75rem; line-height: 1.6;">
                                        <li class="mb-1">
                                            <strong>Wajib Scan QR Code:</strong> Tunjukkan dan scan QR Code akun member Anda
                                            di meja kasir / resepsionis saat tiba untuk absensi check-in kehadiran.
                                        </li>
                                        <li class="mb-1">
                                            <strong>Jam Operasional:</strong> Gym buka setiap <strong>Senin s.d. Sabtu pukul
                                                08:00 - 22:00 WITA</strong> (Hari Minggu libur).
                                        </li>
                                        <li class="mb-1">
                                            <strong>Jaminan Slot:</strong> Kuota kunjungan Anda langsung terkunci dan
                                            terjamin setelah konfirmasi ini disimpan.
                                        </li>
                                        <li>
                                            <strong>Perlengkapan Latihan:</strong> Harap memakai pakaian & sepatu olahraga
                                            bersih serta membawa handuk latihan pribadi demi kenyamanan bersama.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Step 1 -->
                        <div class="modal-footer border-top py-2 px-3 bg-white justify-content-between" id="stepFooter1">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" id="btnNextToStep2">
                                Lanjut ke Konfirmasi & Panduan <i class="bx bx-right-arrow-alt ms-1"></i>
                            </button>
                        </div>

                        <!-- Footer Step 2 -->
                        <div class="modal-footer border-top py-2 px-3 bg-white justify-content-between d-none"
                            id="stepFooter2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                id="btnBackToStep1">
                                <i class="bx bx-left-arrow-alt me-1"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm"
                                id="btnSubmitReservasi">
                                <i class="bx bx-check-circle me-1"></i> Konfirmasi & Amankan Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        #modalBuatReservasi .card {
            margin-bottom: 0 !important;
        }

        .days-grid-10 {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
        }

        .days-grid-10.has-single-card {
            grid-template-columns: minmax(80px, 120px);
        }

        .day-pill-btn {
            padding: 8px 4px;
            min-width: 0;
            width: 100%;
            height: 100%;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease-in-out;
            border-color: #e2e8f0;
            box-sizing: border-box;
        }

        .day-pill-btn .day-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            text-align: center;
        }

        .day-pill-btn .day-slot-badge {
            font-size: 0.6rem !important;
            padding: 2px 2px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        @media (max-width: 576px) {
            .days-grid-10 {
                grid-template-columns: repeat(5, 1fr);
                gap: 4px;
            }

            .days-grid-10.has-single-card {
                grid-template-columns: minmax(75px, 100px);
            }

            .day-pill-btn {
                padding: 6px 2px;
                min-height: 74px;
            }

            .day-pill-btn .day-label {
                font-size: 0.58rem !important;
            }

            .day-pill-btn .day-number {
                font-size: 0.95rem !important;
                margin: 2px 0 !important;
            }

            .day-pill-btn .day-month {
                font-size: 0.55rem !important;
            }

            .day-pill-btn .day-slot-badge {
                font-size: 0.55rem !important;
                padding: 1px 2px !important;
            }

            .package-slot-card {
                padding: 8px !important;
            }

            .modal-dialog {
                margin: 0.5rem;
            }
        }

        .active-day-pill {
            background-color: #696cff !important;
            border-color: #696cff !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(105, 108, 255, 0.35) !important;
        }

        .active-day-pill .day-label,
        .active-day-pill .day-number,
        .active-day-pill .day-month {
            color: #ffffff !important;
        }

        .day-pill-btn:hover:not(.active-day-pill) {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .active-package-card {
            border: 2px solid #696cff !important;
            background-color: rgba(105, 108, 255, 0.04) !important;
            box-shadow: 0 2px 8px rgba(105, 108, 255, 0.12) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        @if (session('reservation_success'))
            Swal.fire({
                icon: 'success',
                title: 'Slot Berhasil Diamankan!',
                html: `
                    <div class="text-start mt-2" style="font-size: 0.88rem;">
                        <p class="text-dark mb-3">Reservasi kunjungan latihan Anda telah berhasil dicatat dan kuota slot kunjungan sudah diamankan.</p>
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                            <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
                                <i class="bx bx-qr-scan fs-5"></i>
                                <span>Panduan Penting Kedatangan:</span>
                            </div>
                            <p class="small text-muted mb-0" style="line-height: 1.5;">
                                Saat tiba di gym, <strong>jangan lupa untuk scan QR Code akun Anda</strong> di meja kasir / resepsionis untuk memvalidasi absensi kehadiran latihan Anda.
                            </p>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#696cff',
                confirmButtonText: 'Saya Mengerti',
                customClass: {
                    popup: 'rounded-4 shadow-lg',
                    confirmButton: 'btn btn-primary rounded-pill px-4'
                },
                buttonsStyling: false
            });
        @endif

        function confirmCancelReservation(btn) {
            Swal.fire({
                title: 'Batalkan Reservasi?',
                text: 'Apakah Anda yakin ingin membatalkan jadwal kunjungan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Kembali',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger rounded-pill px-4',
                    cancelButton: 'btn btn-outline-secondary rounded-pill px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.closest('form').submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalBuatReservasi');
            const visitDateInput = document.getElementById('visit_date');
            const badgeSelectedDate = document.getElementById('badgeSelectedDate');
            const formReservasi = document.getElementById('formBuatReservasi');
            const slotDatesMap = @json($slotDatesMap ?? []);

            function attachDayPillEvents() {
                const dayPills = document.querySelectorAll('.day-pill-btn');
                dayPills.forEach(pill => {
                    pill.addEventListener('click', function() {
                        const selectedDate = this.getAttribute('data-date');
                        const formatted = this.getAttribute('data-formatted');

                        dayPills.forEach(p => {
                            p.classList.remove('active-day-pill');
                            p.classList.add('bg-white');

                            const label = p.querySelector('.day-label');
                            const num = p.querySelector('.day-number');
                            const mon = p.querySelector('.day-month');
                            const badge = p.querySelector('.day-slot-badge');
                            const remaining = parseInt(p.getAttribute('data-remaining') ||
                                '0', 10);
                            const formattedSlot = p.getAttribute('data-formatted-slot') ||
                                '';

                            if (label) label.className =
                                'small fw-semibold day-label text-muted';
                            if (num) num.className =
                                'fw-bold fs-6 my-1 day-number text-dark';
                            if (mon) mon.className = 'day-month text-muted';
                            if (badge) {
                                if (remaining <= 0) {
                                    badge.className =
                                        'badge rounded-pill day-slot-badge mt-1 py-1 px-1 w-100 text-truncate bg-secondary text-white fw-semibold';
                                    badge.textContent = 'Penuh';
                                } else {
                                    badge.className =
                                        'badge rounded-pill day-slot-badge mt-1 py-1 px-1 w-100 text-truncate bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fw-semibold';
                                    badge.textContent = formattedSlot;
                                }
                            }
                        });

                        this.classList.add('active-day-pill');
                        this.classList.remove('bg-white');

                        const activeLabel = this.querySelector('.day-label');
                        const activeNum = this.querySelector('.day-number');
                        const activeMon = this.querySelector('.day-month');
                        const activeBadge = this.querySelector('.day-slot-badge');
                        const activeRemaining = parseInt(this.getAttribute('data-remaining') || '0',
                            10);
                        const activeFormattedSlot = this.getAttribute('data-formatted-slot') || '';

                        if (activeLabel) activeLabel.className =
                            'small fw-semibold day-label text-white';
                        if (activeNum) activeNum.className =
                            'fw-bold fs-6 my-1 day-number text-white';
                        if (activeMon) activeMon.className = 'day-month text-white';
                        if (activeBadge) {
                            activeBadge.className =
                                'badge rounded-pill day-slot-badge mt-1 py-1 px-1 w-100 text-truncate bg-white text-primary fw-bold';
                            activeBadge.textContent = activeRemaining <= 0 ? 'Penuh' :
                                activeFormattedSlot;
                        }

                        if (visitDateInput) {
                            visitDateInput.value = selectedDate;
                        }
                        if (badgeSelectedDate && formatted) {
                            badgeSelectedDate.textContent = formatted;
                        }
                    });
                });
            }

            // Fungsi render kartu hari dinamis berdasarkan paket latihan yang dipilih
            function renderOperationalDays(slotId) {
                const grid = document.getElementById('operationalDaysGrid');
                if (!grid) return;

                const dates = slotDatesMap[slotId] || [];
                if (!dates.length) {
                    grid.innerHTML =
                        '<div class="w-100 text-center text-muted small py-3">Tidak ada jadwal operasional yang tersedia untuk paket ini.</div>';
                    return;
                }

                grid.className = dates.length === 1 ? 'days-grid-10 mb-1 has-single-card' : 'days-grid-10 mb-1';
                const badgeDaysCount = document.getElementById('badgeDaysCount');
                if (badgeDaysCount) {
                    badgeDaysCount.textContent = `${dates.length} Hari Tersedia`;
                }

                const currentVal = visitDateInput ? visitDateInput.value : '';
                let activeIndex = dates.findIndex(d => d.date === currentVal);
                if (activeIndex === -1) {
                    activeIndex = 0;
                }

                let html = '';
                dates.forEach((day, index) => {
                    const isActive = (index === activeIndex);
                    const activeClass = isActive ? 'active-day-pill' : 'bg-white';
                    const labelClass = isActive ? 'text-white' : 'text-muted';
                    const numberClass = isActive ? 'text-white' : 'text-dark';
                    const monthClass = isActive ? 'text-white' : 'text-muted';

                    let badgeClass = '';
                    let badgeText = '';
                    if (isActive) {
                        badgeClass = 'bg-white text-primary fw-bold';
                        badgeText = day.remaining <= 0 ? 'Penuh' : day.formatted;
                    } else if (day.remaining <= 0) {
                        badgeClass = 'bg-secondary text-white fw-semibold';
                        badgeText = 'Penuh';
                    } else {
                        badgeClass =
                            'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fw-semibold';
                        badgeText = day.formatted;
                    }

                    html += `
                        <button type="button"
                            class="btn day-pill-btn text-center rounded-3 border d-flex flex-column align-items-center justify-content-between ${activeClass}"
                            data-date="${day.date}"
                            data-formatted="${day.day_name}, ${day.day_number} ${day.month_name}"
                            data-remaining="${day.remaining}"
                            data-quota="${day.quota}"
                            data-formatted-slot="${day.formatted}"
                            style="cursor: pointer; width: 100%;">
                            <span class="small fw-semibold day-label ${labelClass}">
                                ${day.label}
                            </span>
                            <span class="fw-bold fs-6 my-1 day-number ${numberClass}">
                                ${day.day_number}
                            </span>
                            <span class="day-month ${monthClass}">
                                ${day.month_name}
                            </span>
                            <span class="badge rounded-pill day-slot-badge mt-1 py-1 px-1 w-100 text-truncate ${badgeClass}">
                                ${badgeText}
                            </span>
                        </button>
                    `;
                });

                grid.innerHTML = html;

                const activeDay = dates[activeIndex];
                if (activeDay) {
                    if (visitDateInput) {
                        visitDateInput.value = activeDay.date;
                    }
                    if (badgeSelectedDate) {
                        badgeSelectedDate.textContent =
                            `${activeDay.day_name}, ${activeDay.day_number} ${activeDay.month_name}`;
                    }
                }

                attachDayPillEvents();
            }

            // 1. Klik Card Paket Layanan
            const packageCards = document.querySelectorAll('.package-slot-card');
            packageCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    if (this.getAttribute('data-subscribed') !== '1') {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'info',
                            title: 'Belum Berlangganan',
                            text: 'Paket membership aktif Anda belum mencakup kategori layanan ini. Silakan beli atau perpanjang paket terlebih dahulu.',
                            confirmButtonColor: '#696cff',
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }

                    const radio = this.querySelector('.package-slot-radio');
                    if (radio && !radio.disabled) {
                        radio.checked = true;
                        packageCards.forEach(c => c.classList.remove('active-package-card'));
                        this.classList.add('active-package-card');

                        // Render ulang kartu hari dinamis sesuai paket yang dipilih
                        renderOperationalDays(radio.value);
                    }
                });
            });

            // Inisialisasi event pill yang di-render di server
            attachDayPillEvents();

            // Jalankan saat modal dibuka
            if (modalEl) {
                modalEl.addEventListener('shown.bs.modal', function() {
                    const checkedSlot = document.querySelector('input[name="time_slot_id"]:checked');
                    if (checkedSlot) {
                        renderOperationalDays(checkedSlot.value);
                    }
                });
            }

            // Initial run on page load
            const initialSlot = document.querySelector('input[name="time_slot_id"]:checked');
            if (initialSlot) {
                renderOperationalDays(initialSlot.value);
            }

            // FUNGSI NAVIGASI WIZARD (2 LANGKAH)
            function goToStep(step) {
                const panel1 = document.getElementById('stepPanel1');
                const panel2 = document.getElementById('stepPanel2');

                const footer1 = document.getElementById('stepFooter1');
                const footer2 = document.getElementById('stepFooter2');

                const badge1 = document.getElementById('stepBadge1');
                const badge2 = document.getElementById('stepBadge2');

                if (panel1) panel1.classList.add('d-none');
                if (panel2) panel2.classList.add('d-none');

                if (footer1) footer1.classList.add('d-none');
                if (footer2) footer2.classList.add('d-none');

                const inactiveBadgeClass = 'badge rounded-pill px-3 py-1 bg-white text-dark border';
                const activeBadgeClass = 'badge rounded-pill px-3 py-1 bg-primary text-white shadow-xs';

                if (badge1) badge1.className = inactiveBadgeClass;
                if (badge2) badge2.className = inactiveBadgeClass;

                if (step === 1) {
                    if (panel1) panel1.classList.remove('d-none');
                    if (footer1) footer1.classList.remove('d-none');
                    if (badge1) badge1.className = activeBadgeClass;
                } else if (step === 2) {
                    if (panel2) panel2.classList.remove('d-none');
                    if (footer2) footer2.classList.remove('d-none');
                    if (badge2) badge2.className = activeBadgeClass;
                }
            }

            // 1. Tombol Lanjut ke Konfirmasi & Panduan (Step 1 -> Step 2)
            const btnNextToStep2 = document.getElementById('btnNextToStep2');
            if (btnNextToStep2) {
                btnNextToStep2.addEventListener('click', function() {
                    const checkedSlot = document.querySelector('input[name="time_slot_id"]:checked');
                    if (!checkedSlot) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Paket Layanan',
                            text: 'Silakan pilih salah satu paket layanan yang aktif pada membership Anda.',
                            confirmButtonColor: '#696cff',
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }

                    const visitDate = visitDateInput ? visitDateInput.value : '';
                    if (!visitDate) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pilih Tanggal Kunjungan',
                            text: 'Silakan klik salah satu kartu tanggal latihan yang tersedia.',
                            confirmButtonColor: '#696cff',
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }

                    const notesInput = document.getElementById('notes');
                    const notesVal = notesInput ? notesInput.value.trim() : '';

                    // Ambil detail paket layanan terpilih
                    const slotCard = checkedSlot ? checkedSlot.closest('.package-slot-card') : null;
                    const slotName = slotCard ? slotCard.querySelector('.form-check-label')?.textContent
                        .trim() : '-';
                    const slotTime = slotCard ? slotCard.querySelector(
                        '.text-danger.small, .text-muted.small')?.textContent.trim() : '';

                    // Ambil detail tanggal & kuota dari kartu tanggal aktif
                    const activeDayBtn = document.querySelector('.day-pill-btn.active-day-pill');
                    const formattedDate = activeDayBtn ? activeDayBtn.getAttribute('data-formatted') :
                        visitDate;
                    const slotQuotaBadge = activeDayBtn ? activeDayBtn.querySelector('.day-slot-badge')
                        ?.textContent.trim() : '';

                    // Tampilkan ke ringkasan pemesanan (Step 2)
                    const summaryPackage = document.getElementById('summaryPackage');
                    const summaryTime = document.getElementById('summaryTime');
                    const summaryDate = document.getElementById('summaryDate');
                    const summaryQuota = document.getElementById('summaryQuota');
                    const summaryNotes = document.getElementById('summaryNotes');

                    if (summaryPackage) summaryPackage.textContent = slotName || '-';
                    if (summaryTime) summaryTime.textContent = slotTime || '';
                    if (summaryDate) summaryDate.textContent = formattedDate || visitDate;
                    if (summaryQuota) summaryQuota.textContent = slotQuotaBadge ?
                        `Sisa: ${slotQuotaBadge}` : '-';
                    if (summaryNotes) summaryNotes.textContent = notesVal || 'Tidak ada catatan tambahan';

                    // Navigasi ke Langkah 2 (Konfirmasi & Panduan)
                    goToStep(2);
                });
            }

            // 2. Tombol Kembali ke Paket & Tanggal (Step 2 -> Step 1)
            const btnBackToStep1 = document.getElementById('btnBackToStep1');
            if (btnBackToStep1) {
                btnBackToStep1.addEventListener('click', function() {
                    goToStep(1);
                });
            }

            // 3. Reset kembali ke Langkah 1 saat modal ditutup
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function() {
                    goToStep(1);
                });
            }

            // 4. Submit formulir reservasi
            if (formReservasi) {
                formReservasi.addEventListener('submit', function(e) {
                    const checkedSlot = document.querySelector('input[name="time_slot_id"]:checked');
                    const visitDate = visitDateInput ? visitDateInput.value : '';

                    if (!checkedSlot || !visitDate) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap',
                            text: 'Pilihan paket layanan atau tanggal kunjungan belum ditentukan dengan benar.',
                            confirmButtonColor: '#696cff'
                        });
                        return false;
                    }

                    const submitBtn = document.getElementById('btnSubmitReservasi');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<i class="bx bx-loader-alt bx-spin me-1"></i> Menyimpan Reservasi...';
                    }
                });
            }
        });
    </script>
@endpush
