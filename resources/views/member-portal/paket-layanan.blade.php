@extends('layouts.member')

@section('title', 'Paket Layanan Gym')

@section('content')
    <!-- Header Halaman -->
    <div class="mb-3">
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
            Paket Layanan Gym
        </h5>
        <small class="text-muted" style="font-size: 0.78rem;">
            Pilih paket membership terbaik dan bimbingan pelatih sesuai kebutuhan kebugaran Anda
        </small>
    </div>

    <!-- Nav Pills Tabs: Paket Layanan & Para Trainer -->
    <ul class="nav nav-pills nav-fill p-1 rounded-pill mb-3 border" id="paketTrainerTab" role="tablist"
        style="background-color: #f1f5f9;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold py-2" id="tab-paket-btn" data-bs-toggle="pill"
                data-bs-target="#tab-paket-pane" type="button" role="tab" aria-controls="tab-paket-pane"
                aria-selected="true" style="font-size: 0.82rem;">
                <i class="bx bx-dumbbell me-1"></i> Paket Membership
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2" id="tab-trainer-btn" data-bs-toggle="pill"
                data-bs-target="#tab-trainer-pane" type="button" role="tab" aria-controls="tab-trainer-pane"
                aria-selected="false" style="font-size: 0.82rem;">
                <i class="bx bx-user-pin me-1"></i> Para Trainer
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="paketTrainerTabContent">
        <!-- ============================================================== -->
        <!-- TAB 1: PAKET MEMBERSHIP                                        -->
        <!-- ============================================================== -->
        <div class="tab-pane fade show active" id="tab-paket-pane" role="tabpanel" aria-labelledby="tab-paket-btn">
            <!-- Status Membership Terkini -->
            @if ($activeMembership)
                <div class="card border-0 mb-3 shadow-sm"
                    style="background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%); border-left: 4px solid #dc2626 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-danger px-2 py-1 rounded-pill small mb-1"
                                style="font-size: 0.68rem;">Paket Aktif
                                Saat Ini</span>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">
                                {{ $activeMembership->product?->name ?? 'Membership Reguler' }}
                            </h6>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                Berlaku sampai
                                {{ $activeMembership->end_date ? $activeMembership->end_date->translatedFormat('d F Y') : '-' }}
                                (Sisa {{ $activeMembership->days_remaining }} hari)
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Aktif</span>
                        </div>
                    </div>
                </div>
            @elseif ($pendingMembership)
                <div class="card border-0 mb-3 shadow-sm"
                    style="background-color: #fffbeb; border-left: 4px solid #f59e0b !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bx bx-time fs-4 text-warning flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">Menunggu Verifikasi
                                    Pembayaran</h6>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    Pengajuan <strong>{{ $pendingMembership->product?->name }}</strong>
                                    ({{ $pendingMembership->formatted_price }}) sedang diverifikasi oleh staf kasir.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Daftar Paket Layanan (Card-based) -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark fs-6 mb-2 d-flex align-items-center gap-2">
                    <i class="bx bx-dumbbell text-danger"></i> Pilihan Paket Membership
                </h6>

                <div class="row g-2">
                    @forelse ($products as $prod)
                        @php
                            $activeInfo = $activeMembershipsByProduct->get($prod->id);
                            $durationsData = $prod->activeDurations
                                ->map(function ($d) {
                                    return [
                                        'id' => $d->id,
                                        'value' => $d->duration_value,
                                        'unit' => $d->duration_unit,
                                        'label' => $d->duration_formatted,
                                        'price' => (float) $d->price,
                                        'formatted_price' => $d->formatted_price,
                                        'duration_days' => $d->duration_days,
                                    ];
                                })
                                ->values();
                        @endphp
                        <div class="col-12">
                            <div class="card mb-0 p-3 bg-white border hover-shadow"
                                style="border-radius: 12px; border-color: {{ $activeInfo ? '#fca5a5' : '#f1f5f9' }} !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">

                                <!-- Header: Judul Paket -->
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fw-bold text-dark mb-0 fs-6" style="font-size: 0.95rem;">
                                        {{ $prod->name }}
                                    </h6>
                                    @if ($activeInfo)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0 rounded-pill"
                                            style="font-size: 0.68rem; font-weight: 600;">
                                            <i class="bx bx-check-circle me-1"></i>Paket Aktif Anda
                                        </span>
                                    @endif
                                </div>

                                <!-- Badge Durasi & Kuota Sesi -->
                                <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                        style="font-size: 0.72rem;">
                                        <i class="bx bx-time-five me-1"></i> {{ $prod->activeDurations->count() }} Pilihan
                                        Durasi
                                    </span>
                                    @if ($prod->daily_sessions_limit)
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1 rounded-pill small"
                                            style="font-size: 0.68rem;">
                                            Max {{ $prod->daily_sessions_limit }} Sesi/Hari
                                        </span>
                                    @endif
                                    @if ($activeInfo)
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 rounded-pill small"
                                            style="font-size: 0.68rem; color: #b45309 !important;">
                                            <i class="bx bx-layer-plus me-1"></i>Sisa {{ $activeInfo->days_remaining }} Hari
                                            (s/d
                                            {{ $activeInfo->end_date ? $activeInfo->end_date->translatedFormat('d M Y') : '-' }})
                                        </span>
                                    @endif
                                </div>

                                <!-- Deskripsi Paket -->
                                @if ($prod->description)
                                    <p class="text-muted small mb-2" style="font-size: 0.75rem; line-height: 1.4;">
                                        {{ $prod->description }}
                                    </p>
                                @endif

                                <!-- Footer Card: Harga di Kiri & Tombol Pilih Paket di Kanan -->
                                <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <span class="text-muted d-block small" style="font-size: 0.65rem;">Tarif
                                            Mulai</span>
                                        <span class="fw-bold text-danger fs-6 d-block" style="white-space: nowrap;">
                                            {{ $prod->formatted_price }}
                                        </span>
                                    </div>
                                    <button type="button"
                                        class="btn btn-danger btn-sm rounded-pill px-3 py-1 btn-pilih-paket flex-shrink-0"
                                        data-id="{{ $prod->id }}" data-name="{{ $prod->name }}"
                                        data-price="{{ $prod->formatted_price }}"
                                        data-description="{{ $prod->description ?? '' }}"
                                        data-durations="{{ json_encode($durationsData) }}"
                                        data-has-active="{{ $activeInfo ? '1' : '0' }}"
                                        data-active-end="{{ $activeInfo && $activeInfo->end_date ? $activeInfo->end_date->format('Y-m-d') : '' }}"
                                        data-active-end-formatted="{{ $activeInfo && $activeInfo->end_date ? $activeInfo->end_date->translatedFormat('d F Y') : '' }}"
                                        data-active-remaining="{{ $activeInfo ? $activeInfo->days_remaining : 0 }}"
                                        data-next-start="{{ $activeInfo && $activeInfo->end_date ? $activeInfo->end_date->copy()->addDay()->format('Y-m-d') : '' }}"
                                        data-next-start-formatted="{{ $activeInfo && $activeInfo->end_date ? $activeInfo->end_date->copy()->addDay()->translatedFormat('d F Y') : '' }}"
                                        style="font-size: 0.78rem; white-space: nowrap;">
                                        @if ($activeInfo)
                                            <i class="bx bx-layer-plus me-1"></i> Perpanjang Paket
                                        @else
                                            <i class="bx bx-cart me-1"></i> Pilih Paket
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card p-4 text-center border bg-white">
                                <i class="bx bx-package fs-1 text-secondary opacity-50 mb-2"></i>
                                <p class="text-muted small mb-0">Belum ada paket layanan yang aktif.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 2: PARA TRAINER (Layout justify-between dengan CTA WA)     -->
        <!-- ============================================================== -->
        <div class="tab-pane fade" id="tab-trainer-pane" role="tabpanel" aria-labelledby="tab-trainer-btn">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold text-dark fs-6 mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-user-check text-danger"></i> Instruktur & Personal Trainer
                </h6>
                <span
                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1"
                    style="font-size: 0.7rem;">
                    {{ $trainers->count() }} Trainer Aktif
                </span>
            </div>
            <p class="text-muted small mb-3" style="font-size: 0.75rem; line-height: 1.4;">
                Konsultasikan program latihan, bimbingan gerakan, dan target kebugaran Anda langsung dengan instruktur
                profesional kami via WhatsApp.
            </p>

            <div class="row g-2">
                @forelse ($trainers as $tr)
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $tr->phone ?? '');
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                        $trainerName = $tr->user?->name ?? 'Coach';
                        $waMessage = rawurlencode(
                            "Halo {$trainerName}, saya member Indo Fitness Gym Sport ingin konsultasi mengenai program latihan personal trainer.",
                        );
                        $waUrl = !empty($cleanPhone)
                            ? "https://wa.me/{$cleanPhone}?text={$waMessage}"
                            : "https://wa.me/6285467321554?text={$waMessage}";
                    @endphp
                    <div class="col-12">
                        <div class="card mb-0 p-3 bg-white border hover-shadow"
                            style="border-radius: 12px; border-color: #f1f5f9 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <!-- Bagian Kiri: Foto, Nama & Spesialis -->
                                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                                    @if ($tr->user?->avatar)
                                        <img src="{{ asset('storage/' . $tr->user->avatar) }}"
                                            alt="{{ $tr->user->name }}"
                                            class="rounded-circle object-fit-cover shadow-sm flex-shrink-0"
                                            style="width: 48px; height: 48px; border: 2px solid #fee2e2;">
                                    @else
                                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold shadow-sm flex-shrink-0"
                                            style="width: 48px; height: 48px; font-size: 1.05rem; border: 2px solid #fee2e2;">
                                            {{ strtoupper(substr($tr->user?->name ?? 'T', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="text-truncate">
                                        <h6 class="fw-bold text-dark mb-0 fs-6 text-truncate" style="font-size: 0.92rem;">
                                            {{ $tr->user?->name ?? 'Coach' }}
                                        </h6>
                                        <span class="text-danger small fw-semibold d-block text-truncate"
                                            style="font-size: 0.76rem;">
                                            {{ $tr->specialization ?? 'Personal Trainer & Fitness Coach' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Bagian Kanan: Tombol CTA WA (justify-between) -->
                                <a href="{{ $waUrl }}" target="_blank"
                                    class="btn btn-success btn-sm rounded-pill px-3 py-1 flex-shrink-0 d-inline-flex align-items-center shadow-sm"
                                    style="font-size: 0.78rem; font-weight: 600; background-color: #25D366; border-color: #25D366; white-space: nowrap;">
                                    <i class="bx bxl-whatsapp fs-5 me-1"></i> Chat WA
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card p-4 text-center border bg-white text-muted small">
                            <i class="bx bx-user-x fs-1 text-secondary opacity-50 mb-2"></i>
                            <p class="mb-0">Belum ada pelatih/trainer yang terdaftar saat ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: Step 1 Pilihan Durasi Paket                           -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep1" tabindex="-1" aria-labelledby="modalCheckoutStep1Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep1Label">
                        <i class="bx bx-time-five text-danger me-1"></i> Pilih Durasi Paket
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator (Step 1 dari 4) -->
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">1</span>
                            <span class="small fw-bold text-danger" style="font-size: 0.72rem;">Durasi</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">2</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Metode</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">3</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bayar</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">4</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- Info Paket yang Dipilih -->
                    <div class="card border rounded-3 mb-3 shadow-sm"
                        style="background: #ffffff; border-color: #fecaca !important;">
                        <div class="card-body p-3">
                            <span class="text-uppercase fw-bold text-danger d-block mb-1"
                                style="font-size: 0.65rem; letter-spacing: 0.5px;">Paket Gym Dipilih</span>
                            <h6 class="fw-bold text-dark mb-1 fs-6" id="m1_product_name">-</h6>
                            <p class="text-muted small mb-0" id="m1_product_desc"
                                style="font-size: 0.74rem; line-height: 1.4;"></p>
                        </div>
                    </div>

                    <!-- Callout Akumulasi Durasi (Hanya muncul jika member sudah memiliki paket aktif ini) -->
                    <div id="m1_accumulation_box"
                        class="alert alert-warning border border-warning border-opacity-25 rounded-3 p-3 mb-3 d-none"
                        style="background-color: #fffbeb;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bx bx-layer-plus fs-4 text-warning flex-shrink-0 mt-0"></i>
                            <div style="font-size: 0.76rem; line-height: 1.45;">
                                <div class="d-flex align-items-center gap-1 mb-1">
                                    <strong class="text-dark">Sistem Akumulasi Aktif</strong>
                                    <span class="badge bg-warning text-dark px-2 py-0 rounded-pill small fw-semibold"
                                        style="font-size: 0.65rem;">Otomatis Diperpanjang</span>
                                </div>
                                <span class="text-secondary d-block">
                                    Paket ini saat ini masih aktif hingga <strong><span
                                            id="m1_accum_end_date">-</span></strong> (sisa <strong><span
                                            id="m1_accum_remaining">-</span> hari</strong>).
                                </span>
                                <span class="text-dark fw-medium d-block mt-1">
                                    Durasi baru yang Anda pilih akan <span
                                        class="badge bg-warning text-dark px-1 py-0 fw-semibold">diakumulasikan</span>
                                    mulai tanggal <strong><span id="m1_accum_start_date">-</span></strong> tanpa jeda masa
                                    aktif.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Durasi Tersedia -->
                    <div class="mb-2">
                        <label
                            class="form-label small fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                            <span>Pilihan Durasi Paket <span class="text-danger">*</span></span>
                            <small class="text-muted fw-normal" style="font-size: 0.7rem;">Pilih salah satu durasi</small>
                        </label>

                        <div class="d-flex flex-column gap-2" id="durationOptionList">
                            <!-- Diisi secara dinamis melalui JavaScript -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between gap-2 bg-white"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnStep1Next"
                        class="btn btn-sm btn-danger rounded-pill px-3 px-sm-4 text-nowrap" disabled
                        onclick="goToStep2()">
                        Lanjut ke Metode Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: Step 2 Pilih Metode Pembayaran (PG Style)             -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep2" tabindex="-1" aria-labelledby="modalCheckoutStep2Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep2Label">
                        <i class="bx bx-credit-card text-danger me-1"></i> Pilih Metode Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator (Step 2 dari 4) -->
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-success text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                    class="bx bx-check"></i></span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Durasi</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">2</span>
                            <span class="small fw-bold text-danger" style="font-size: 0.72rem;">Metode</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">3</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bayar</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">4</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- Detail Produk yang Dipilih & Durasi Terpilih -->
                    <div class="card border rounded-3 mb-3 shadow-sm"
                        style="background: #ffffff; border-color: #fecaca !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start justify-content-between mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="text-uppercase fw-bold text-danger d-block"
                                        style="font-size: 0.65rem; letter-spacing: 0.5px;">Paket Gym Dipilih</span>
                                    <h6 class="fw-bold text-dark mb-0 fs-6" id="m2_product_name">-</h6>
                                </div>
                                <span
                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                    id="m2_product_badge_durasi" style="font-size: 0.7rem;">
                                    <i class="bx bx-time-five me-1"></i> <span id="m2_product_duration_days">-</span>
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                style="background-color: #fff5f5; border: 1px solid #fee2e2;">
                                <i class="bx bx-calendar text-danger fs-5 flex-shrink-0"></i>
                                <div style="line-height: 1.3;">
                                    <span class="text-muted d-block" style="font-size: 0.65rem;">Masa Aktif Paket:</span>
                                    <span class="fw-bold text-dark" id="m2_product_duration_range"
                                        style="font-size: 0.74rem;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Metode Pembayaran Berbentuk Payment Gateway -->
                    <div class="mb-2">
                        <label
                            class="form-label small fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                            <span>Metode Pembayaran Tersedia <span class="text-danger">*</span></span>
                            <small class="text-muted fw-normal" style="font-size: 0.7rem;">Pilih salah satu</small>
                        </label>

                        <div class="d-flex flex-column gap-2" id="pgMethodList">
                            @forelse ($paymentMethods as $pm)
                                <div class="pg-method-card card mb-0 p-3 rounded-3 cursor-pointer"
                                    data-id="{{ $pm->id }}" data-name="{{ $pm->name }}"
                                    data-type="{{ $pm->type }}" data-account="{{ $pm->account_number }}"
                                    data-holder="{{ $pm->account_name }}" data-qr="{{ $pm->qr_image_url }}"
                                    style="cursor: pointer; transition: all 0.2s;">

                                    <!-- Baris 1: Ikon + Nama Metode di Kiri, Indikator Pilih di Kanan -->
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                                            <div class="pg-icon-box rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width: 38px; height: 38px; background-color: {{ $pm->type === 'qris' ? 'rgba(220, 38, 38, 0.1)' : ($pm->type === 'bank_transfer' ? 'rgba(13, 110, 253, 0.1)' : 'rgba(25, 135, 84, 0.1)') }};">
                                                @if ($pm->type === 'qris')
                                                    <i class="bx bx-qr-scan fs-4 text-danger"></i>
                                                @elseif ($pm->type === 'bank_transfer')
                                                    <i class="bx bxs-bank fs-4 text-primary"></i>
                                                @elseif ($pm->type === 'ewallet')
                                                    <i class="bx bx-wallet fs-4 text-success"></i>
                                                @else
                                                    <i class="bx bx-credit-card fs-4 text-secondary"></i>
                                                @endif
                                            </div>
                                            <div style="min-width: 0;">
                                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                                                    {{ $pm->name }}
                                                </div>
                                                <small class="text-muted d-block text-truncate"
                                                    style="font-size: 0.70rem;">
                                                    {{ $pm->type_label }} @if ($pm->account_number)
                                                        &bull; {{ $pm->account_number }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="pg-check-indicator rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0 ms-2"
                                            style="width: 22px; height: 22px; border-color: #cbd5e1; transition: all 0.2s;">
                                            <i class="bx bx-check text-white d-none" style="font-size: 15px;"></i>
                                        </div>
                                    </div>

                                    <!-- Baris 2: Harga Sesuai Durasi Terpilih -->
                                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-baseline gap-1">
                                            <span class="fw-bold text-danger fs-6 pg-card-price"
                                                style="white-space: nowrap;">Rp 0</span>
                                        </div>
                                        <div class="text-end">
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0"
                                                style="font-size: 0.68rem; font-weight: 500;">
                                                Biaya: Gratis
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-center text-muted border rounded-3 bg-white small">
                                    <i class="bx bx-info-circle me-1 text-warning"></i> Belum ada metode pembayaran yang
                                    aktif saat ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between gap-2 bg-white"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-sm btn-light border" onclick="backToStep1()">
                        <i class="bx bx-chevron-left me-1"></i> Ganti Durasi
                    </button>
                    <button type="button" id="btnStep2Next"
                        class="btn btn-sm btn-danger rounded-pill px-3 px-sm-4 text-nowrap" disabled
                        onclick="goToStep3()">
                        Lanjut ke Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: Step 3 Detail Pembayaran (Nomor Rekening / QRIS)      -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep3" tabindex="-1" aria-labelledby="modalCheckoutStep3Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep3Label">
                        <i class="bx bx-wallet text-danger me-1"></i> Detail Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator (Step 3 dari 4) -->
                    <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-success text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                    class="bx bx-check"></i></span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Durasi</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-success text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                    class="bx bx-check"></i></span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Metode</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">3</span>
                            <span class="small fw-bold text-danger" style="font-size: 0.72rem;">Bayar</span>
                        </div>
                        <div style="width: 18px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border-color: #e2e8f0 !important;">4</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- Nominal Tagihan -->
                    <div class="card border border-danger border-opacity-25 p-3 rounded-3 mb-3 text-center shadow-sm"
                        style="background-color: #fff5f5;">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">Total yang Harus
                            Ditransfer:</span>
                        <h4 class="fw-bold text-danger mb-1" id="m3_product_price">Rp 0</h4>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                id="m3_product_badge" style="font-size: 0.72rem;">-</span>
                        </div>
                    </div>

                    <!-- Box Detail Rekening Bank -->
                    <div id="m3_bank_box" class="card border p-3 rounded-3 mb-3 bg-white text-center shadow-sm d-none"
                        style="border-color: #f1f5f9 !important;">
                        <div class="text-center mb-1">
                            <span class="text-muted fw-semibold text-uppercase"
                                style="font-size: 0.78rem; letter-spacing: 0.5px;" id="m3_bank_name">
                                BCA
                            </span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 my-2">
                            <span class="fw-bold text-dark fs-4 font-monospace text-break" id="m3_account_number"
                                style="letter-spacing: 1px;">-</span>
                            <button type="button" id="btnCopyAccount"
                                class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 d-inline-flex align-items-center text-nowrap"
                                style="font-size: 0.75rem; line-height: 1;" onclick="copyAccountNumber()"
                                title="Salin Nomor Rekening">
                                <i class="bx bx-copy me-1"></i> Salin
                            </button>
                        </div>

                        <div class="text-secondary small fw-medium" id="m3_account_holder" style="font-size: 0.85rem;">
                            -
                        </div>
                    </div>

                    <!-- Box Detail QRIS -->
                    <div id="m3_qris_box" class="card border p-3 rounded-3 mb-3 bg-white text-center shadow-sm d-none"
                        style="border-color: #f1f5f9 !important;">
                        <span
                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small mb-2 mx-auto">
                            <i class="bx bx-qr-scan me-1"></i> Pembayaran QRIS Resmi IFGS
                        </span>

                        <div class="p-2 bg-white rounded border d-inline-block shadow-sm mx-auto mb-2"
                            style="max-width: 240px; border-color: #fee2e2 !important;">
                            <img id="m3_qris_image" src="{{ asset('img/qris-ifgs.svg') }}" alt="QRIS IFGS"
                                class="img-fluid rounded" style="max-height: 220px; width: 100%; object-fit: contain;">
                        </div>

                        <div class="fw-bold text-dark small" id="m3_qris_holder">Indo Fitness Gym Sport</div>
                        <small class="text-muted font-monospace d-block mb-2" id="m3_qris_account">NMID:
                            ID1024300928172</small>

                        <div class="alert alert-light border small text-muted text-start mb-0"
                            style="font-size: 0.72rem; line-height: 1.4;">
                            <i class="bx bx-info-circle text-danger me-1"></i> Buka aplikasi mobile banking (BCA, Mandiri,
                            BRI) atau e-wallet (GoPay, OVO, Dana) Anda, lalu scan kode QRIS di atas.
                        </div>
                    </div>

                    <!-- Petunjuk Ringkas -->
                    <div class="p-3 rounded-3 bg-white border small text-muted shadow-sm"
                        style="border-color: #f1f5f9 !important; font-size: 0.72rem; line-height: 1.5;">
                        <div class="fw-bold text-dark mb-1">Petunjuk Pembayaran:</div>
                        <ol class="mb-0 ps-3">
                            <li>Lakukan transfer tepat sesuai nominal yang tertera di atas.</li>
                            <li>Simpan struk atau tangkapan layar (screenshot) bukti transfer Anda.</li>
                            <li>Klik tombol di bawah untuk mengunggah bukti pembayaran.</li>
                        </ol>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between gap-2 bg-white"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-sm btn-light border" onclick="backToStep2()">
                        <i class="bx bx-chevron-left me-1"></i> Ganti Metode
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 px-sm-4 text-nowrap"
                        onclick="goToStep4()">
                        Upload Bukti Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: Step 4 Upload Bukti Pembayaran                        -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep4" tabindex="-1" aria-labelledby="modalCheckoutStep4Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep4Label">
                        <i class="bx bx-upload text-danger me-1"></i> Upload Bukti Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formCheckoutMembership" action="{{ route('memberships.order') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="final_product_id">
                    <input type="hidden" name="product_duration_id" id="final_product_duration_id">
                    <input type="hidden" name="payment_method_id" id="final_payment_method_id">
                    <input type="hidden" name="start_date" id="final_start_date">

                    <div class="modal-body p-3">
                        <!-- Stepper Indicator (Step 4 dari 4) -->
                        <div class="d-flex align-items-center justify-content-center gap-1 mb-3">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white shadow-sm"
                                    style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                        class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Durasi</span>
                            </div>
                            <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white shadow-sm"
                                    style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                        class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Metode</span>
                            </div>
                            <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white shadow-sm"
                                    style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i
                                        class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Bayar</span>
                            </div>
                            <div style="width: 18px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                    style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">4</span>
                                <span class="small fw-bold text-danger" style="font-size: 0.72rem;">Bukti</span>
                            </div>
                        </div>

                        <!-- Ringkasan Singkat Pesanan & Detail Total -->
                        <div class="card border rounded-3 mb-3 shadow-sm"
                            style="background-color: #ffffff; border-color: #fee2e2 !important;">
                            <div class="card-body p-3">
                                <!-- Baris 1: Paket & Total Tagihan -->
                                <div
                                    class="d-flex align-items-start justify-content-between pb-2 mb-2 border-bottom gap-2">
                                    <div style="min-width: 0;">
                                        <span class="text-muted d-block"
                                            style="font-size: 0.68rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Paket
                                            & Durasi:</span>
                                        <strong class="text-dark fs-6 text-truncate d-block"
                                            id="m4_product_name">-</strong>
                                        <small class="text-danger d-block fw-semibold" id="m4_product_duration"
                                            style="font-size: 0.75rem;">-</small>
                                    </div>
                                    <div class="text-end flex-shrink-0">
                                        <span class="text-muted d-block"
                                            style="font-size: 0.68rem; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Total
                                            Tagihan:</span>
                                        <strong class="text-danger fs-6 text-nowrap" id="m4_product_price">Rp 0</strong>
                                    </div>
                                </div>

                                <!-- Baris 2: Metode Pembayaran -->
                                <div class="d-flex align-items-center justify-content-between py-1"
                                    style="font-size: 0.78rem;">
                                    <span class="text-muted">Metode Pembayaran:</span>
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-semibold"
                                        id="m4_payment_name">-</span>
                                </div>

                                <!-- Baris 3: Sistem Akumulasi (Jika Berlaku) -->
                                <div id="m4_accum_row"
                                    class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top mt-2 gap-1 d-none"
                                    style="font-size: 0.76rem;">
                                    <span class="text-muted d-inline-flex align-items-center">
                                        <i class="bx bx-layer-plus text-warning fs-5 me-1"></i>Sistem Akumulasi:
                                    </span>
                                    <span
                                        class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-40 rounded-pill px-2 py-1 fw-semibold"
                                        id="m4_accum_badge" style="font-size: 0.72rem;">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dropzone Upload Area -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Upload Bukti Transfer / Pembayaran <span class="text-danger">*</span>
                            </label>

                            <input type="file" id="order_payment_proof" name="payment_proof"
                                accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none" required>

                            <div id="dropzoneArea"
                                class="border border-2 border-dashed rounded-3 p-3 text-center bg-white cursor-pointer"
                                style="border-color: #fca5a5 !important; cursor: pointer; transition: all 0.2s;">

                                <div id="uploadPlaceholder">
                                    <div class="mx-auto bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center mb-2"
                                        style="width: 48px; height: 48px;">
                                        <i class="bx bx-cloud-upload fs-3"></i>
                                    </div>
                                    <div class="fw-bold text-dark small mb-1">Pilih Foto Bukti Transfer</div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                        Klik di sini untuk memilih foto atau screenshot (Maksimal 5MB)
                                    </small>
                                </div>

                                <div id="uploadPreviewBox" class="d-none">
                                    <img id="previewImage" src="" alt="Preview Bukti"
                                        class="img-fluid rounded border shadow-sm mb-2"
                                        style="max-height: 160px; object-fit: contain;">
                                    <div class="small fw-bold text-dark text-truncate px-2" id="previewFileName">
                                    </div>
                                    <button type="button" id="btnChangeProof"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 mt-1"
                                        style="font-size: 0.72rem;">
                                        <i class="bx bx-refresh me-1"></i> Ganti Foto
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Format yang didukung:
                                JPG,
                                PNG, WEBP.</small>
                        </div>

                        <!-- Catatan Tambahan (Opsional) -->
                        <div class="mb-2">
                            <label for="order_notes" class="form-label small fw-bold text-dark mb-1">Catatan Tambahan
                                (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="order_notes" name="notes"
                                placeholder="Contoh: Transfer atas nama John via BCA">
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between gap-2 bg-white"
                        style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-sm btn-light border" onclick="backToStep3()">
                            <i class="bx bx-chevron-left me-1"></i> Kembali
                        </button>
                        <button type="submit" id="btnSubmitOrder"
                            class="btn btn-sm btn-danger rounded-pill px-3 px-sm-4 text-nowrap">
                            <i class="bx bx-send me-1"></i> Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        #paketTrainerTab .nav-link {
            color: #64748b;
            transition: all 0.2s ease-in-out;
        }

        #paketTrainerTab .nav-link.active {
            background-color: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
        }

        .duration-option-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.2s ease-in-out;
        }

        .duration-option-card:hover {
            border-color: #dc2626 !important;
            background-color: #fff5f5 !important;
        }

        .duration-option-card.selected {
            border-color: #dc2626 !important;
            background-color: #fff5f5 !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.12) !important;
        }

        .duration-option-card.selected .duration-check-indicator {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        .duration-option-card.selected .duration-check-indicator i {
            display: inline-block !important;
        }

        .pg-method-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0 !important;
            transition: all 0.2s ease-in-out;
        }

        .pg-method-card:hover {
            border-color: #dc2626 !important;
            background-color: #fff5f5 !important;
        }

        .pg-method-card.selected {
            border-color: #dc2626 !important;
            background-color: #fff5f5 !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.12) !important;
        }

        .pg-method-card.selected .pg-check-indicator {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        .pg-method-card.selected .pg-check-indicator i {
            display: inline-block !important;
        }

        #dropzoneArea:hover {
            border-color: #dc2626 !important;
            background-color: #fff5f5 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data membership aktif member dikelompokkan berdasarkan product_id dari backend
            const activeMembershipsData = @json($activeMembershipsData ?? []);

            // Helper kalkulasi tanggal berakhir paket (mendukung sistem akumulasi)
            function calculateAccumulatedEndDate(startDateStr, unit, value) {
                const parts = startDateStr.split('-');
                const year = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1;
                const day = parseInt(parts[2], 10);

                const d = new Date(year, month, day);

                if (unit === 'day') {
                    if (value > 1) {
                        d.setDate(d.getDate() + (value - 1));
                    }
                } else if (unit === 'week') {
                    d.setDate(d.getDate() + (value * 7));
                } else if (unit === 'month') {
                    d.setMonth(d.getMonth() + value);
                } else if (unit === 'year') {
                    d.setFullYear(d.getFullYear() + value);
                } else if (unit === 'lifetime') {
                    d.setFullYear(d.getFullYear() + 99);
                } else {
                    d.setMonth(d.getMonth() + value);
                }

                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');

                const indonesianMonths = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                return {
                    iso: `${yyyy}-${mm}-${dd}`,
                    formatted: `${d.getDate()} ${indonesianMonths[d.getMonth()]} ${yyyy}`
                };
            }

            // Modal Elements (4 Steps)
            const modalEl1 = document.getElementById('modalCheckoutStep1');
            const modalEl2 = document.getElementById('modalCheckoutStep2');
            const modalEl3 = document.getElementById('modalCheckoutStep3');
            const modalEl4 = document.getElementById('modalCheckoutStep4');

            // State Data
            let selectedProduct = null;
            let selectedDuration = null;
            let selectedPaymentMethod = null;

            // Transisi Antar Modal Tanpa Glitch Backdrop Bootstrap
            function switchModal(fromModalEl, toModalEl) {
                const fromInstance = bootstrap.Modal.getInstance(fromModalEl);
                const toInstance = bootstrap.Modal.getOrCreateInstance(toModalEl);

                if (fromInstance) {
                    fromModalEl.addEventListener('hidden.bs.modal', function onHidden() {
                        fromModalEl.removeEventListener('hidden.bs.modal', onHidden);
                        toInstance.show();
                    });
                    fromInstance.hide();
                } else {
                    toInstance.show();
                }
            }

            // Step 1 -> Step 2
            window.goToStep2 = function() {
                if (!selectedDuration) return;

                // Isi Data di Modal 2 (Metode Pembayaran)
                document.getElementById('m2_product_name').textContent = selectedProduct.name;
                document.getElementById('m2_product_badge_durasi').innerHTML =
                    `<i class="bx bx-time-five me-1"></i> ${selectedDuration.label}`;

                if (selectedProduct.activeInfo) {
                    document.getElementById('m2_product_duration_range').textContent =
                        `Diakumulasikan: ${selectedProduct.activeInfo.next_start_date_formatted} s/d ${selectedDuration.calculatedEndDateFormatted} (+${selectedDuration.duration_days} Hari)`;
                } else {
                    document.getElementById('m2_product_duration_range').textContent =
                        `${selectedDuration.label} (${selectedDuration.duration_days} Hari) s/d ${selectedDuration.calculatedEndDateFormatted}`;
                }

                // Update detail harga di dalam setiap kartu metode pembayaran sesuai durasi terpilih
                document.querySelectorAll('.pg-card-price').forEach(function(el) {
                    el.textContent = selectedDuration.formatted_price;
                });

                // Reset pilihan metode pembayaran di Step 2
                selectedPaymentMethod = null;
                document.querySelectorAll('.pg-method-card').forEach(c => c.classList.remove('selected'));
                document.getElementById('btnStep2Next').disabled = true;

                switchModal(modalEl1, modalEl2);
            };

            // Step 2 -> Step 1
            window.backToStep1 = function() {
                switchModal(modalEl2, modalEl1);
            };

            // Step 2 -> Step 3
            window.goToStep3 = function() {
                if (!selectedPaymentMethod || !selectedDuration) return;

                // Isi Data di Modal 3 (Detail Pembayaran)
                document.getElementById('m3_product_price').textContent = selectedDuration.formatted_price;
                if (selectedProduct.activeInfo) {
                    document.getElementById('m3_product_badge').textContent =
                        `${selectedProduct.name} • ${selectedDuration.label} (Perpanjangan Akumulasi)`;
                } else {
                    document.getElementById('m3_product_badge').textContent =
                        `${selectedProduct.name} • ${selectedDuration.label}`;
                }

                const bankBox = document.getElementById('m3_bank_box');
                const qrisBox = document.getElementById('m3_qris_box');

                if (selectedPaymentMethod.type === 'qris') {
                    bankBox.classList.add('d-none');
                    qrisBox.classList.remove('d-none');

                    if (selectedPaymentMethod.qr) {
                        document.getElementById('m3_qris_image').src = selectedPaymentMethod.qr;
                    }
                    document.getElementById('m3_qris_holder').textContent = selectedPaymentMethod.holder ||
                        'Indo Fitness Gym Sport';
                    document.getElementById('m3_qris_account').textContent = selectedPaymentMethod.account ||
                        'NMID: ID1024300928172';
                } else {
                    qrisBox.classList.add('d-none');
                    bankBox.classList.remove('d-none');

                    let bankName = selectedPaymentMethod.name;
                    let accountNumber = selectedPaymentMethod.account || '-';

                    const match = accountNumber.match(/^(.*?)\s*\(([^)]+)\)$/);
                    if (match) {
                        accountNumber = match[1].trim();
                        const extractedBank = match[2].trim();
                        if (bankName.toLowerCase().includes('transfer') || bankName.toLowerCase().includes(
                                'bank')) {
                            bankName = extractedBank;
                        } else {
                            bankName = `${bankName} (${extractedBank})`;
                        }
                    }

                    document.getElementById('m3_bank_name').textContent = bankName;
                    document.getElementById('m3_account_number').textContent = accountNumber;
                    document.getElementById('m3_account_holder').textContent = selectedPaymentMethod.holder ||
                        'Indo Fitness Gym';
                }

                switchModal(modalEl2, modalEl3);
            };

            // Step 3 -> Step 2
            window.backToStep2 = function() {
                switchModal(modalEl3, modalEl2);
            };

            // Step 3 -> Step 4
            window.goToStep4 = function() {
                // Isi Ringkasan di Modal 4 (Upload Bukti)
                document.getElementById('m4_product_name').textContent = selectedProduct.name;
                document.getElementById('m4_product_duration').textContent =
                    `${selectedDuration.label} (${selectedDuration.formatted_price})`;
                document.getElementById('m4_payment_name').textContent = selectedPaymentMethod.name;
                document.getElementById('m4_product_price').textContent = selectedDuration.formatted_price;

                document.getElementById('final_product_id').value = selectedProduct.id;
                document.getElementById('final_product_duration_id').value = selectedDuration.id;
                document.getElementById('final_payment_method_id').value = selectedPaymentMethod.id;

                const accumRow = document.getElementById('m4_accum_row');
                if (selectedProduct.activeInfo) {
                    document.getElementById('final_start_date').value = selectedProduct.activeInfo
                        .next_start_date;
                    if (accumRow) {
                        accumRow.classList.remove('d-none');
                        document.getElementById('m4_accum_badge').textContent =
                            `Berlaku s/d ${selectedDuration.calculatedEndDateFormatted}`;
                    }
                } else {
                    document.getElementById('final_start_date').value = new Date().toISOString().split('T')[0];
                    if (accumRow) {
                        accumRow.classList.add('d-none');
                    }
                }

                switchModal(modalEl3, modalEl4);
            };

            // Step 4 -> Step 3
            window.backToStep3 = function() {
                switchModal(modalEl4, modalEl3);
            };

            // Salin Nomor Rekening
            window.copyAccountNumber = function() {
                const accountNum = document.getElementById('m3_account_number').textContent.trim();
                if (!accountNum || accountNum === '-') return;

                const copyBtn = document.getElementById('btnCopyAccount');
                const originalHtml = copyBtn.innerHTML;

                navigator.clipboard.writeText(accountNum).then(() => {
                    copyBtn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                    copyBtn.classList.remove('btn-outline-danger');
                    copyBtn.classList.add('btn-success');
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHtml;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-danger');
                    }, 2000);
                }).catch(() => {
                    const temp = document.createElement('textarea');
                    temp.value = accountNum;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                    copyBtn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHtml;
                    }, 2000);
                });
            };

            // Tombol "Pilih Paket" di Daftar Produk
            document.querySelectorAll('.btn-pilih-paket').forEach(function(button) {
                button.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'), 10);
                    const name = this.getAttribute('data-name');
                    const desc = this.getAttribute('data-description') || '';
                    let durations = [];

                    try {
                        durations = JSON.parse(this.getAttribute('data-durations')) || [];
                    } catch (e) {
                        durations = [];
                    }

                    const activeInfo = activeMembershipsData[id] || null;

                    selectedProduct = {
                        id,
                        name,
                        desc,
                        durations,
                        activeInfo
                    };
                    selectedDuration = null;
                    selectedPaymentMethod = null;

                    // Isi data modal 1 (Durasi)
                    document.getElementById('m1_product_name').textContent = name;
                    const descEl = document.getElementById('m1_product_desc');
                    if (desc) {
                        descEl.textContent = desc;
                        descEl.style.display = 'block';
                    } else {
                        descEl.style.display = 'none';
                    }

                    // Callout Akumulasi Durasi
                    const accumBox = document.getElementById('m1_accumulation_box');
                    if (activeInfo) {
                        accumBox.classList.remove('d-none');
                        document.getElementById('m1_accum_end_date').textContent = activeInfo
                            .end_date_formatted;
                        document.getElementById('m1_accum_remaining').textContent = activeInfo
                            .days_remaining;
                        document.getElementById('m1_accum_start_date').textContent = activeInfo
                            .next_start_date_formatted;
                    } else {
                        accumBox.classList.add('d-none');
                    }

                    // Render daftar opsi durasi
                    const durationListContainer = document.getElementById('durationOptionList');
                    durationListContainer.innerHTML = '';

                    if (durations.length === 0) {
                        durationListContainer.innerHTML = `
                            <div class="p-3 text-center text-muted border rounded-3 bg-white small">
                                Tidak ada opsi durasi aktif untuk paket ini.
                            </div>
                        `;
                        document.getElementById('btnStep1Next').disabled = true;
                    } else {
                        durations.forEach(function(dur, index) {
                            const baseStartDate = activeInfo ? activeInfo.next_start_date :
                                new Date().toISOString().split('T')[0];
                            const endCalc = calculateAccumulatedEndDate(baseStartDate, dur
                                .unit, dur.value);
                            dur.calculatedEndDate = endCalc.iso;
                            dur.calculatedEndDateFormatted = endCalc.formatted;

                            const optionEl = document.createElement('div');
                            optionEl.className =
                                'duration-option-card card mb-0 p-3 rounded-3 cursor-pointer';
                            optionEl.setAttribute('data-id', dur.id);
                            optionEl.style.cursor = 'pointer';

                            let durationDescHtml = '';
                            if (activeInfo) {
                                durationDescHtml = `
                                    <small class="text-success fw-semibold d-block" style="font-size: 0.72rem;">
                                        <i class="bx bx-layer-plus me-1"></i>Diakumulasikan: Berlaku s/d ${dur.calculatedEndDateFormatted}
                                    </small>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">+${dur.duration_days} hari mulai ${activeInfo.next_start_date_formatted}</small>
                                `;
                            } else {
                                durationDescHtml = `
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Masa aktif ${dur.duration_days} hari (Berlaku s/d ${dur.calculatedEndDateFormatted})</small>
                                `;
                            }

                            optionEl.innerHTML = `
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex align-items-center gap-2 gap-sm-3" style="min-width: 0;">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 38px; height: 38px; background-color: ${activeInfo ? 'rgba(234, 179, 8, 0.15)' : 'rgba(220, 38, 38, 0.1)'};">
                                            <i class="bx ${activeInfo ? 'bx-layer-plus text-warning' : 'bx-time-five text-danger'} fs-4"></i>
                                        </div>
                                        <div style="min-width: 0;">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${dur.label}</div>
                                            ${durationDescHtml}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 gap-sm-3 flex-shrink-0">
                                        <span class="fw-bold text-danger fs-6 text-nowrap">${dur.formatted_price}</span>
                                        <div class="duration-check-indicator rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 22px; height: 22px; border-color: #cbd5e1; transition: all 0.2s;">
                                            <i class="bx bx-check text-white d-none" style="font-size: 15px;"></i>
                                        </div>
                                    </div>
                                </div>
                            `;

                            optionEl.addEventListener('click', function() {
                                document.querySelectorAll('.duration-option-card')
                                    .forEach(c => c.classList.remove('selected'));
                                this.classList.add('selected');
                                selectedDuration = dur;
                                document.getElementById('btnStep1Next').disabled =
                                    false;
                            });

                            durationListContainer.appendChild(optionEl);

                            // Auto-select opsi durasi pertama sebagai default
                            if (index === 0) {
                                optionEl.classList.add('selected');
                                selectedDuration = dur;
                                document.getElementById('btnStep1Next').disabled = false;
                            }
                        });
                    }

                    // Reset form & upload preview
                    const form = document.getElementById('formCheckoutMembership');
                    if (form) form.reset();

                    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
                    const uploadPreviewBox = document.getElementById('uploadPreviewBox');
                    const previewImage = document.getElementById('previewImage');

                    if (uploadPlaceholder) uploadPlaceholder.classList.remove('d-none');
                    if (uploadPreviewBox) uploadPreviewBox.classList.add('d-none');
                    if (previewImage) previewImage.src = '';

                    // Buka Modal 1 (Pilih Durasi)
                    const modal1 = bootstrap.Modal.getOrCreateInstance(modalEl1);
                    modal1.show();
                });
            });

            // Klik Pilihan Metode Pembayaran (PG Style di Modal 2)
            document.querySelectorAll('.pg-method-card').forEach(function(card) {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.pg-method-card').forEach(c => c.classList.remove(
                        'selected'));
                    this.classList.add('selected');

                    selectedPaymentMethod = {
                        id: this.getAttribute('data-id'),
                        name: this.getAttribute('data-name'),
                        type: this.getAttribute('data-type'),
                        account: this.getAttribute('data-account'),
                        holder: this.getAttribute('data-holder'),
                        qr: this.getAttribute('data-qr')
                    };

                    document.getElementById('btnStep2Next').disabled = false;
                });
            });

            // File Upload Handler & Live Preview di Modal 4
            const fileInput = document.getElementById('order_payment_proof');
            const dropzoneArea = document.getElementById('dropzoneArea');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const uploadPreviewBox = document.getElementById('uploadPreviewBox');
            const previewImage = document.getElementById('previewImage');
            const previewFileName = document.getElementById('previewFileName');
            const btnChangeProof = document.getElementById('btnChangeProof');

            if (dropzoneArea) {
                dropzoneArea.addEventListener('click', function(e) {
                    if (e.target !== btnChangeProof) {
                        fileInput.click();
                    }
                });
            }

            if (btnChangeProof) {
                btnChangeProof.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];

                        // Validasi ukuran 5MB
                        if (file.size > 5 * 1024 * 1024) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ukuran Terlalu Besar',
                                text: 'Ukuran file bukti pembayaran maksimal adalah 5MB.',
                                confirmButtonColor: '#dc2626'
                            });
                            this.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewFileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(
                                1) + ' KB)';
                            uploadPlaceholder.classList.add('d-none');
                            uploadPreviewBox.classList.remove('d-none');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Submit Form via AJAX + SweetAlert2
            const form = document.getElementById('formCheckoutMembership');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (!fileInput.files || !fileInput.files[0]) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bukti Belum Dipilih',
                            text: 'Silakan unggah foto atau screenshot bukti transfer terlebih dahulu.',
                            confirmButtonColor: '#dc2626'
                        });
                        return;
                    }

                    const submitBtn = document.getElementById('btnSubmitOrder');
                    const originalHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim...';

                    const formData = new FormData(form);

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) {
                                throw new Error(data.message ||
                                    'Terjadi kesalahan saat memproses pesanan.');
                            }
                            return data;
                        })
                        .then(data => {
                            const modal4 = bootstrap.Modal.getInstance(modalEl4);
                            if (modal4) modal4.hide();

                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Diterima!',
                                html: `
                                <p class="mb-2">Bukti pembayaran Anda telah berhasil kami terima.</p>
                                <div class="alert alert-light border small text-muted text-start mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    <i class="bx bx-time-five text-warning me-1"></i> Mohon menunggu validasi dan verifikasi dari kasir/staf IFGS. Status membership Anda akan aktif setelah diverifikasi.
                                </div>
                            `,
                                confirmButtonText: 'OK, Mengerti',
                                confirmButtonColor: '#dc2626',
                                customClass: {
                                    confirmButton: 'btn btn-danger rounded-pill px-4'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHtml;

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengirim',
                                text: error.message ||
                                    'Terjadi kendala saat mengirim bukti pembayaran. Silakan coba lagi.',
                                confirmButtonColor: '#dc2626'
                            });
                        });
                });
            }

            // Fallback SweetAlert jika halaman reload membawa session('success')
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Diterima!',
                    html: `
                        <p class="mb-2">{{ session('success') }}</p>
                        <div class="alert alert-light border small text-muted text-start mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                            <i class="bx bx-time-five text-warning me-1"></i> Bukti transfer Anda telah diterima dan sedang menunggu validasi oleh kasir.
                        </div>
                    `,
                    confirmButtonText: 'OK, Mengerti',
                    confirmButtonColor: '#dc2626',
                    customClass: {
                        confirmButton: 'btn btn-danger rounded-pill px-4'
                    },
                    buttonsStyling: false
                });
            @endif
        });
    </script>
@endpush
