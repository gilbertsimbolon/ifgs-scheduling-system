@extends('layouts.member')

@section('title', 'Paket Layanan Gym')

@section('content')
    <!-- Header Halaman -->
    <div class="mb-3">
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">
            Paket Layanan Gym
        </h5>
        <small class="text-muted" style="font-size: 0.78rem;">
            Pilih paket membership terbaik sesuai kebutuhan kebugaran Anda
        </small>
    </div>

    <!-- Status Membership Terkini -->
    @if ($activeMembership)
        <div class="card border-0 mb-3 shadow-sm"
            style="background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%); border-left: 4px solid #dc2626 !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-danger px-2 py-1 rounded-pill small mb-1" style="font-size: 0.68rem;">Paket Aktif
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
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">Menunggu Verifikasi Pembayaran</h6>
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
                <div class="col-12">
                    <div class="card mb-0 p-3 bg-white border hover-shadow"
                        style="border-radius: 12px; border-color: #f1f5f9 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">

                        <!-- Header: Judul Paket -->
                        <h6 class="fw-bold text-dark mb-1 fs-6" style="font-size: 0.95rem;">
                            {{ $prod->name }}
                        </h6>

                        <!-- Badge Durasi (Hanya Jumlah Hari) & Kuota Sesi -->
                        <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                style="font-size: 0.72rem;">
                                <i class="bx bx-time-five me-1"></i> {{ $prod->duration_days }} Hari
                            </span>
                            @if ($prod->daily_sessions_limit)
                                <span
                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1 rounded-pill small"
                                    style="font-size: 0.68rem;">
                                    Max {{ $prod->daily_sessions_limit }} Sesi/Hari
                                </span>
                            @endif
                        </div>

                        <!-- Deskripsi Paket (jika ada) -->
                        @if ($prod->description)
                            <p class="text-muted small mb-2" style="font-size: 0.75rem; line-height: 1.4;">
                                {{ $prod->description }}
                            </p>
                        @endif

                        <!-- Footer Card: Harga di Kiri & Tombol Pilih Paket di Kanan (Sejajar) -->
                        <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2">
                            <div>
                                <span class="fw-bold text-danger fs-6 d-block" style="white-space: nowrap;">
                                    {{ $prod->formatted_price }}
                                </span>
                            </div>
                            <button type="button"
                                class="btn btn-danger btn-sm rounded-pill px-3 py-1 btn-pilih-paket flex-shrink-0"
                                data-id="{{ $prod->id }}" data-name="{{ $prod->name }}"
                                data-price="{{ $prod->formatted_price }}" data-duration="{{ $prod->duration_days }}"
                                data-duration-range="{{ $prod->duration_range_formatted }}"
                                data-description="{{ $prod->description ?? '' }}"
                                style="font-size: 0.78rem; white-space: nowrap;">
                                <i class="bx bx-cart me-1"></i> Pilih Paket
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

    <!-- ============================================================== -->
    <!-- MODAL 1: Detail Produk yang Dibeli & Pilih Metode (PG Style)   -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep1" tabindex="-1" aria-labelledby="modalCheckoutStep1Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep1Label">
                        <i class="bx bx-cart text-danger me-1"></i> Pilih Metode Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700;">1</span>
                            <span class="small fw-bold text-danger" style="font-size: 0.75rem;">Metode</span>
                        </div>
                        <div style="width: 28px; height: 2px; background-color: #fca5a5;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; border-color: #e2e8f0 !important;">2</span>
                            <span class="small text-muted" style="font-size: 0.75rem;">Bayar</span>
                        </div>
                        <div style="width: 28px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; border-color: #e2e8f0 !important;">3</span>
                            <span class="small text-muted" style="font-size: 0.75rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- 1. Detail Produk yang Dibeli -->
                    <div class="card border rounded-3 mb-3 shadow-sm"
                        style="background: #ffffff; border-color: #fecaca !important;">
                        <div class="card-body p-3">
                            <!-- Info Paket Header -->
                            <div class="d-flex align-items-start justify-content-between mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="text-uppercase fw-bold text-danger d-block"
                                        style="font-size: 0.65rem; letter-spacing: 0.5px;">Paket Gym Dipilih</span>
                                    <h6 class="fw-bold text-dark mb-0 fs-6" id="m1_product_name">-</h6>
                                </div>
                                <span
                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                    id="m1_product_badge_durasi" style="font-size: 0.7rem;">
                                    <i class="bx bx-time-five me-1"></i> <span id="m1_product_duration_days">-</span>
                                </span>
                            </div>

                            <!-- Masa Aktif / Rentang Tanggal Small -->
                            <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                                style="background-color: #fff5f5; border: 1px solid #fee2e2;">
                                <i class="bx bx-calendar text-danger fs-5 flex-shrink-0"></i>
                                <div style="line-height: 1.3;">
                                    <span class="text-muted d-block" style="font-size: 0.65rem;">Masa Aktif Paket:</span>
                                    <span class="fw-bold text-dark" id="m1_product_duration_range"
                                        style="font-size: 0.74rem;">-</span>
                                </div>
                            </div>

                            <p class="text-muted mb-0 mt-2" id="m1_product_desc"
                                style="font-size: 0.73rem; line-height: 1.4; display: none;"></p>
                        </div>
                    </div>

                    <!-- 2. Pilihan Metode Pembayaran Berbentuk Payment Gateway (No Dropdown) -->
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

                                    <!-- Baris 2: Harga Dipindahkan ke Bawah Sejajar Info Biaya -->
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
                                <div class="alert alert-warning small p-2 mb-0">
                                    Belum ada metode pembayaran yang aktif saat ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between bg-white"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnStep1Next" class="btn btn-sm btn-danger rounded-pill px-4" disabled
                        onclick="goToStep2()">
                        Lanjut ke Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: Detail Metode Pembayaran (Nomor Rekening / QRIS)       -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep2" tabindex="-1" aria-labelledby="modalCheckoutStep2Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep2Label">
                        <i class="bx bx-credit-card text-danger me-1"></i> Detail Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-success text-white shadow-sm"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem;"><i
                                    class="bx bx-check"></i></span>
                            <span class="small text-muted" style="font-size: 0.75rem;">Metode</span>
                        </div>
                        <div style="width: 28px; height: 2px; background-color: #198754;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700;">2</span>
                            <span class="small fw-bold text-danger" style="font-size: 0.75rem;">Bayar</span>
                        </div>
                        <div style="width: 28px; height: 2px; background-color: #e2e8f0;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-white text-muted border"
                                style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; border-color: #e2e8f0 !important;">3</span>
                            <span class="small text-muted" style="font-size: 0.75rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- Nominal Tagihan -->
                    <div class="card border border-danger border-opacity-25 p-3 rounded-3 mb-3 text-center shadow-sm"
                        style="background-color: #fff5f5;">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">Total yang Harus
                            Ditransfer:</span>
                        <h4 class="fw-bold text-danger mb-1" id="m2_product_price">Rp 0</h4>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small"
                                id="m2_product_badge" style="font-size: 0.72rem;">-</span>
                        </div>
                    </div>

                    <!-- Box Detail Rekening Bank -->
                    <div id="m2_bank_box" class="card border p-3 rounded-3 mb-3 bg-white text-center shadow-sm d-none"
                        style="border-color: #f1f5f9 !important;">
                        <!-- 1. Nama Rekening / Bank (Small, Rata Tengah) -->
                        <div class="text-center mb-1">
                            <span class="text-muted fw-semibold text-uppercase"
                                style="font-size: 0.78rem; letter-spacing: 0.5px;" id="m2_bank_name">
                                BCA
                            </span>
                        </div>

                        <!-- 2. Nomor Rekening (Copyable, Rata Tengah) -->
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2">
                            <span class="fw-bold text-dark fs-4 font-monospace" id="m2_account_number"
                                style="letter-spacing: 1px;">-</span>
                            <button type="button" id="btnCopyAccount"
                                class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 d-inline-flex align-items-center"
                                style="font-size: 0.75rem; line-height: 1;" onclick="copyAccountNumber()"
                                title="Salin Nomor Rekening">
                                <i class="bx bx-copy me-1"></i> Salin
                            </button>
                        </div>

                        <!-- 3. Nama Pemilik Rekening (Rata Tengah, Tanpa Teks 'Atas Nama') -->
                        <div class="text-secondary small fw-medium" id="m2_account_holder" style="font-size: 0.85rem;">
                            -
                        </div>
                    </div>

                    <!-- Box Detail QRIS -->
                    <div id="m2_qris_box" class="card border p-3 rounded-3 mb-3 bg-white text-center shadow-sm d-none"
                        style="border-color: #f1f5f9 !important;">
                        <span
                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small mb-2 mx-auto">
                            <i class="bx bx-qr-scan me-1"></i> Pembayaran QRIS Resmi IFGS
                        </span>

                        <div class="p-2 bg-white rounded border d-inline-block shadow-sm mx-auto mb-2"
                            style="max-width: 240px; border-color: #fee2e2 !important;">
                            <img id="m2_qris_image" src="{{ asset('img/qris-ifgs.svg') }}" alt="QRIS IFGS"
                                class="img-fluid rounded" style="max-height: 220px; width: 100%; object-fit: contain;">
                        </div>

                        <div class="fw-bold text-dark small" id="m2_qris_holder">Indo Fitness Gym Sport</div>
                        <small class="text-muted font-monospace d-block mb-2" id="m2_qris_account">NMID:
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
                            <li>Lakukan transfer sesuai nominal tepat yang tertera di atas.</li>
                            <li>Simpan resi / struk atau tangkapan layar (screenshot) bukti transfer Anda.</li>
                            <li>Klik tombol di bawah untuk melanjutkan ke pengunggahan bukti pembayaran.</li>
                        </ol>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between bg-white"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-sm btn-light border" onclick="backToStep1()">
                        <i class="bx bx-chevron-left me-1"></i> Ganti Metode
                    </button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-4" onclick="goToStep3()">
                        Upload Bukti Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: Upload Bukti Pembayaran                               -->
    <!-- ============================================================== -->
    <div class="modal fade" id="modalCheckoutStep3" tabindex="-1" aria-labelledby="modalCheckoutStep3Label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-2 px-3 bg-white">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep3Label">
                        <i class="bx bx-upload text-danger me-1"></i> Upload Bukti Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formCheckoutMembership" action="{{ route('memberships.order') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="final_product_id">
                    <input type="hidden" name="payment_method_id" id="final_payment_method_id">
                    <input type="hidden" name="start_date" id="final_start_date">

                    <div class="modal-body p-3">
                        <!-- Stepper Indicator -->
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white shadow-sm"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem;"><i
                                        class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.75rem;">Metode</span>
                            </div>
                            <div style="width: 28px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white shadow-sm"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem;"><i
                                        class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.75rem;">Bayar</span>
                            </div>
                            <div style="width: 28px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-danger text-white shadow-sm"
                                    style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700;">3</span>
                                <span class="small fw-bold text-danger" style="font-size: 0.75rem;">Bukti</span>
                            </div>
                        </div>

                        <!-- Ringkasan Singkat Pesanan & Detail Total -->
                        <div class="card border rounded-3 mb-3 shadow-sm"
                            style="background-color: #ffffff; border-color: #fee2e2 !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.65rem;">Paket &
                                            Durasi:</span>
                                        <strong class="text-dark" id="m3_product_name">-</strong>
                                        <small class="text-danger d-block fw-semibold" id="m3_product_duration_range"
                                            style="font-size: 0.72rem;">-</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted d-block" style="font-size: 0.65rem;">Total Tagihan:</span>
                                        <strong class="text-danger fs-6" id="m3_product_price">Rp 0</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between"
                                    style="font-size: 0.74rem;">
                                    <span class="text-muted">Metode Pembayaran:</span>
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill"
                                        id="m3_payment_name">-</span>
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
                                    <div class="small fw-bold text-dark text-truncate px-2" id="previewFileName"></div>
                                    <button type="button" id="btnChangeProof"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 mt-1"
                                        style="font-size: 0.72rem;">
                                        <i class="bx bx-refresh me-1"></i> Ganti Foto
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Format yang didukung: JPG,
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

                    <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between bg-white"
                        style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-sm btn-light border" onclick="backToStep2()">
                            <i class="bx bx-chevron-left me-1"></i> Kembali
                        </button>
                        <button type="submit" id="btnSubmitOrder" class="btn btn-sm btn-danger rounded-pill px-4">
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
            // Modal Elements
            const modalEl1 = document.getElementById('modalCheckoutStep1');
            const modalEl2 = document.getElementById('modalCheckoutStep2');
            const modalEl3 = document.getElementById('modalCheckoutStep3');

            // State Data
            let selectedProduct = null;
            let selectedPaymentMethod = null;

            // Transisi Antar Modal Tanpa Glitch
            function switchModal(fromModalEl, toModalEl) {
                const fromInstance = bootstrap.Modal.getInstance(fromModalEl);
                const toInstance = bootstrap.Modal.getOrCreateInstance(toModalEl);

                if (fromInstance) {
                    fromInstance.hide();
                    fromModalEl.addEventListener('hidden.bs.modal', function onHidden() {
                        fromModalEl.removeEventListener('hidden.bs.modal', onHidden);
                        toInstance.show();
                    });
                } else {
                    toInstance.show();
                }
            }

            window.goToStep2 = function() {
                if (!selectedPaymentMethod) return;

                // Isi Data di Modal 2
                document.getElementById('m2_product_price').textContent = selectedProduct.price;
                document.getElementById('m2_product_badge').textContent = selectedProduct.name + ' • ' +
                    selectedProduct.durationRange;

                const bankBox = document.getElementById('m2_bank_box');
                const qrisBox = document.getElementById('m2_qris_box');

                if (selectedPaymentMethod.type === 'qris') {
                    bankBox.classList.add('d-none');
                    qrisBox.classList.remove('d-none');

                    if (selectedPaymentMethod.qr) {
                        document.getElementById('m2_qris_image').src = selectedPaymentMethod.qr;
                    }
                    document.getElementById('m2_qris_holder').textContent = selectedPaymentMethod.holder ||
                        'Indo Fitness Gym Sport';
                    document.getElementById('m2_qris_account').textContent = selectedPaymentMethod.account ||
                        'NMID: ID1024300928172';
                } else {
                    qrisBox.classList.add('d-none');
                    bankBox.classList.remove('d-none');

                    let bankName = selectedPaymentMethod.name;
                    let accountNumber = selectedPaymentMethod.account || '-';

                    // Ekstrak nama bank jika nomor rekening dalam format "123-456-7890 (BCA)"
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

                    document.getElementById('m2_bank_name').textContent = bankName;
                    document.getElementById('m2_account_number').textContent = accountNumber;
                    document.getElementById('m2_account_holder').textContent = selectedPaymentMethod.holder ||
                        'Indo Fitness Gym';
                }

                switchModal(modalEl1, modalEl2);
            };

            window.backToStep1 = function() {
                switchModal(modalEl2, modalEl1);
            };

            window.goToStep3 = function() {
                // Isi Ringkasan di Modal 3
                document.getElementById('m3_product_name').textContent = selectedProduct.name;
                document.getElementById('m3_product_duration_range').textContent = selectedProduct
                    .durationRange;
                document.getElementById('m3_payment_name').textContent = selectedPaymentMethod.name;
                document.getElementById('m3_product_price').textContent = selectedProduct.price;

                document.getElementById('final_product_id').value = selectedProduct.id;
                document.getElementById('final_payment_method_id').value = selectedPaymentMethod.id;
                document.getElementById('final_start_date').value = new Date().toISOString().split('T')[0];

                switchModal(modalEl2, modalEl3);
            };

            window.backToStep2 = function() {
                switchModal(modalEl3, modalEl2);
            };

            // Salin Nomor Rekening
            window.copyAccountNumber = function() {
                const accountNum = document.getElementById('m2_account_number').textContent.trim();
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
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const price = this.getAttribute('data-price');
                    const durationDays = this.getAttribute('data-duration') || '1';
                    const durationRange = this.getAttribute('data-duration-range') || (
                        durationDays + ' Hari');
                    const desc = this.getAttribute('data-description') || '';

                    selectedProduct = {
                        id,
                        name,
                        price,
                        durationDays,
                        durationRange,
                        desc
                    };

                    // Isi data modal 1
                    document.getElementById('m1_product_name').textContent = name;
                    document.getElementById('m1_product_duration_days').textContent = durationDays +
                        ' Hari';
                    document.getElementById('m1_product_duration_range').textContent =
                        durationRange;

                    // Update detail harga di dalam setiap kartu metode pembayaran (posisi yang ditandai user)
                    document.querySelectorAll('.pg-card-price').forEach(function(el) {
                        el.textContent = price;
                    });

                    const descEl = document.getElementById('m1_product_desc');
                    if (desc) {
                        descEl.textContent = desc;
                        descEl.style.display = 'block';
                    } else {
                        descEl.style.display = 'none';
                    }

                    // Reset seleksi metode
                    selectedPaymentMethod = null;
                    document.querySelectorAll('.pg-method-card').forEach(c => c.classList.remove(
                        'selected'));
                    document.getElementById('btnStep1Next').disabled = true;

                    // Reset form & upload preview
                    const form = document.getElementById('formCheckoutMembership');
                    if (form) form.reset();

                    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
                    const uploadPreviewBox = document.getElementById('uploadPreviewBox');
                    const previewImage = document.getElementById('previewImage');

                    if (uploadPlaceholder) uploadPlaceholder.classList.remove('d-none');
                    if (uploadPreviewBox) uploadPreviewBox.classList.add('d-none');
                    if (previewImage) previewImage.src = '';

                    // Buka Modal 1
                    const modal1 = bootstrap.Modal.getOrCreateInstance(modalEl1);
                    modal1.show();
                });
            });

            // Klik Pilihan Metode Pembayaran (PG Style)
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

                    document.getElementById('btnStep1Next').disabled = false;
                });
            });

            // File Upload Handler & Live Preview
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
                            const modal3 = bootstrap.Modal.getInstance(modalEl3);
                            if (modal3) modal3.hide();

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
