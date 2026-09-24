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
        <div class="card border-0 bg-primary bg-opacity-10 mb-3 shadow-none">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-primary px-2 py-1 rounded-pill small mb-1">Paket Aktif Saat Ini</span>
                    <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem;">
                        {{ $activeMembership->product?->name ?? 'Membership Reguler' }}
                    </h6>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        Berlaku sampai {{ $activeMembership->end_date ? $activeMembership->end_date->translatedFormat('d F Y') : '-' }} (Sisa {{ $activeMembership->days_remaining }} hari)
                    </small>
                </div>
                <div class="text-end">
                    <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: 0.7rem;">Aktif</span>
                </div>
            </div>
        </div>
    @elseif ($pendingMembership)
        <div class="card border-0 bg-warning bg-opacity-10 mb-3 shadow-none">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bx bx-time fs-4 text-warning flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 0.88rem;">Menunggu Verifikasi Pembayaran</h6>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            Pengajuan <strong>{{ $pendingMembership->product?->name }}</strong> ({{ $pendingMembership->formatted_price }}) sedang diverifikasi oleh staf kasir.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Daftar Paket Layanan (Card-based) -->
    <div class="mb-4">
        <h6 class="fw-bold text-dark fs-6 mb-2 d-flex align-items-center gap-2">
            <i class="bx bx-dumbbell text-primary"></i> Pilihan Paket Membership
        </h6>

        <div class="row g-2">
            @forelse ($products as $prod)
                <div class="col-12">
                    <div class="card mb-0 p-3 bg-white border hover-shadow">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                    {{ $prod->name }}
                                </h6>
                                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.68rem;">
                                    Durasi: {{ $prod->duration_days }} Hari
                                </span>
                                @if ($prod->daily_sessions_limit)
                                    <span class="badge bg-light text-secondary border px-2 py-1 ms-1" style="font-size: 0.68rem;">
                                        Max {{ $prod->daily_sessions_limit }} Sesi/Hari
                                    </span>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary fs-6 d-block">
                                    {{ $prod->formatted_price }}
                                </span>
                            </div>
                        </div>

                        @if ($prod->description)
                            <p class="text-muted small mb-2" style="font-size: 0.75rem; line-height: 1.4;">
                                {{ $prod->description }}
                            </p>
                        @endif

                        <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                @if ($prod->duration_days <= 1)
                                    <i class="bx bx-check-circle text-success me-1"></i> Tanpa perlu reservasi
                                @else
                                    <i class="bx bx-calendar-check text-primary me-1"></i> Termasuk reservasi slot
                                @endif
                            </span>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 py-1 btn-pilih-paket"
                                data-id="{{ $prod->id }}"
                                data-name="{{ $prod->name }}"
                                data-price="{{ $prod->formatted_price }}"
                                data-duration="{{ $prod->duration_days }}"
                                style="font-size: 0.78rem;">
                                <i class="bx bx-cart me-1"></i> Pilih Paket
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card p-4 text-center border">
                        <i class="bx bx-package fs-1 text-secondary opacity-50 mb-2"></i>
                        <p class="text-muted small mb-0">Belum ada paket layanan yang aktif.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Langganan Membership Saya -->
    <div class="card mb-3">
        <div class="card-header py-2 px-3 bg-white border-bottom">
            <h6 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                <i class="bx bx-receipt text-primary"></i> Riwayat Langganan Saya
            </h6>
        </div>
        <div class="card-body p-3">
            @if ($myMemberships->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach ($myMemberships as $ms)
                        <div class="p-2 border rounded-3 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-dark small">{{ $ms->product?->name ?? 'Paket Gym' }}</span>
                                <div>
                                    @if ($ms->status === \App\Models\Membership::STATUS_ACTIVE)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill" style="font-size: 0.68rem;">Aktif</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_PENDING)
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill" style="font-size: 0.68rem;">Pending</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_REJECTED)
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill" style="font-size: 0.68rem;">Ditolak</span>
                                    @elseif ($ms->status === \App\Models\Membership::STATUS_EXPIRED)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill" style="font-size: 0.68rem;">Kedaluwarsa</span>
                                    @else
                                        <span class="badge bg-light text-muted rounded-pill" style="font-size: 0.68rem;">{{ ucfirst($ms->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.72rem;">
                                <span>{{ $ms->formatted_price }} &bull; {{ $ms->paymentMethod?->name ?? 'Transfer' }}</span>
                                <span>{{ $ms->start_date ? $ms->start_date->format('d/m/y') : '-' }} s/d {{ $ms->end_date ? $ms->end_date->format('d/m/y') : '-' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small text-center mb-0 py-2">Belum ada riwayat pembelian paket membership.</p>
            @endif
        </div>
    </div>

    <!-- Modal Order Membership (Clean, Polos & User Friendly) -->
    <div class="modal fade" id="modalOrderMembership" tabindex="-1" aria-labelledby="modalOrderMembershipLabel" aria-hidden="true">
    <!-- ========================================== -->
    <!-- MODAL 1: Detail Produk & Pilih Metode (PG)  -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalCheckoutStep1" tabindex="-1" aria-labelledby="modalCheckoutStep1Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalOrderMembershipLabel">
                        <i class="bx bx-cart text-primary me-1"></i> Pemesanan Paket Gym
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep1Label">
                        <i class="bx bx-cart text-primary me-1"></i> Pilih Metode Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('memberships.order') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="order_product_id">
                <div class="modal-body p-3">
                    <!-- Stepper Indicator -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-primary text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">1</span>
                            <span class="small fw-bold text-primary" style="font-size: 0.72rem;">Metode</span>
                        </div>
                        <div style="width: 24px; height: 2px; background-color: #cbd5e1;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-light text-muted border" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">2</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bayar</span>
                        </div>
                        <div style="width: 24px; height: 2px; background-color: #cbd5e1;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-light text-muted border" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">3</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bukti</span>
                        </div>
                    </div>

                    <div class="modal-body p-3">
                        <!-- 1. Ringkasan Paket Terpilih -->
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Paket Dipilih:</span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0" id="order_product_name">-</h6>
                                <span class="fw-bold text-primary fs-6" id="order_product_price">Rp 0</span>
                    <!-- 1. Detail Produk yang Dibeli -->
                    <div class="card border p-3 rounded-3 mb-3 bg-light shadow-none">
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.7rem;">Paket Dipilih:</span>
                                <h6 class="fw-bold text-dark mb-0" id="m1_product_name">-</h6>
                            </div>
                            <small class="text-muted" id="order_product_duration" style="font-size: 0.72rem;"></small>
                            <span class="fw-bold text-primary fs-6" id="m1_product_price">Rp 0</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                            <span class="badge bg-white text-secondary border px-2 py-1" id="m1_product_duration" style="font-size: 0.68rem;">-</span>
                            <span class="text-muted small" id="m1_product_desc" style="font-size: 0.72rem;"></span>
                        </div>
                    </div>

                        <!-- 2. Pilihan Metode Pembayaran -->
                        <div class="mb-3">
                            <label for="order_payment_method" class="form-label small fw-bold text-dark">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="order_payment_method" name="payment_method_id" required>
                                <option value="">-- Pilih Rekening Pembayaran --</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}"
                                        data-name="{{ $pm->name }}"
                                        data-account="{{ $pm->account_number }}"
                                        data-holder="{{ $pm->account_name }}">
                                        {{ $pm->name }} @if($pm->account_number) ({{ $pm->account_number }}) @endif
                                    </option>
                                @endforeach
                            </select>
                    <!-- 2. Pilihan Metode Pembayaran Berbentuk Payment Gateway (No Dropdown) -->
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                            <span>Metode Pembayaran Tersedia <span class="text-danger">*</span></span>
                            <small class="text-muted fw-normal" style="font-size: 0.7rem;">Pilih salah satu</small>
                        </label>

                        <div class="d-flex flex-column gap-2" id="pgMethodList">
                            @forelse ($paymentMethods as $pm)
                                <div class="pg-method-card card mb-0 p-3 border rounded-3 cursor-pointer"
                                    data-id="{{ $pm->id }}"
                                    data-name="{{ $pm->name }}"
                                    data-type="{{ $pm->type }}"
                                    data-account="{{ $pm->account_number }}"
                                    data-holder="{{ $pm->account_name }}"
                                    data-qr="{{ $pm->qr_image_url }}"
                                    style="cursor: pointer; transition: all 0.2s;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="pg-icon-box rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width: 42px; height: 42px; background-color: {{ $pm->type === 'qris' ? 'rgba(220, 38, 38, 0.1)' : ($pm->type === 'bank_transfer' ? 'rgba(13, 110, 253, 0.1)' : 'rgba(25, 135, 84, 0.1)') }};">
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
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $pm->name }}</div>
                                                <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                    {{ $pm->type_label }} @if($pm->account_number) &bull; {{ $pm->account_number }} @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="pg-check-indicator rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width: 22px; height: 22px; border-color: #cbd5e1; transition: all 0.2s;">
                                            <i class="bx bx-check text-white d-none" style="font-size: 15px;"></i>
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

                        <!-- Box Detail Rekening Tujuan Transfer -->
                        <div id="rekeningDetailBox" class="p-3 rounded-3 border bg-white mb-3 d-none">
                            <span class="text-muted small d-block mb-1" style="font-size: 0.7rem;">Transfer Pembayaran ke:</span>
                            <div class="fw-bold text-dark" id="pmBankName" style="font-size: 0.88rem;">-</div>
                            <div class="d-flex align-items-center justify-content-between my-1">
                                <span class="text-primary fw-bold font-monospace fs-6" id="pmAccountNumber">-</span>
                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnStep1Next" class="btn btn-sm btn-primary rounded-pill px-4" disabled onclick="goToStep2()">
                        Lanjut ke Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: Detail Metode Pembayaran          -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalCheckoutStep2" tabindex="-1" aria-labelledby="modalCheckoutStep2Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep2Label">
                        <i class="bx bx-credit-card text-primary me-1"></i> Detail Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Stepper Indicator -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-success text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i class="bx bx-check"></i></span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Metode</span>
                        </div>
                        <div style="width: 24px; height: 2px; background-color: #198754;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-primary text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">2</span>
                            <span class="small fw-bold text-primary" style="font-size: 0.72rem;">Bayar</span>
                        </div>
                        <div style="width: 24px; height: 2px; background-color: #cbd5e1;"></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge rounded-circle bg-light text-muted border" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">3</span>
                            <span class="small text-muted" style="font-size: 0.72rem;">Bukti</span>
                        </div>
                    </div>

                    <!-- Nominal Tagihan -->
                    <div class="card border border-primary bg-primary bg-opacity-10 p-3 rounded-3 mb-3 text-center shadow-none">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.72rem;">Total yang Harus Ditransfer:</span>
                        <h4 class="fw-bold text-primary mb-1" id="m2_product_price">Rp 0</h4>
                        <small class="text-muted" id="m2_product_badge">-</small>
                    </div>

                    <!-- Box Detail Rekening / QRIS -->
                    <div id="m2_bank_box" class="card border p-3 rounded-3 mb-3 bg-white d-none">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                                <i class="bx bxs-bank fs-5"></i>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Atas Nama: <strong class="text-dark" id="pmAccountHolder">-</strong></small>
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.7rem;">Rekening Tujuan:</span>
                                <h6 class="fw-bold text-dark mb-0" id="m2_bank_name">Bank Transfer</h6>
                            </div>
                        </div>

                        <!-- 3. Upload Bukti Pembayaran -->
                        <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.68rem;">Nomor Rekening:</span>
                                <span class="fw-bold text-dark fs-5 font-monospace" id="m2_account_number">-</span>
                            </div>
                            <button type="button" id="btnCopyAccount" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" onclick="copyAccountNumber()">
                                <i class="bx bx-copy me-1"></i> Salin
                            </button>
                        </div>

                        <div class="small text-muted" style="font-size: 0.75rem;">
                            Atas Nama: <strong class="text-dark" id="m2_account_holder">-</strong>
                        </div>
                    </div>

                    <!-- Box Detail QRIS -->
                    <div id="m2_qris_box" class="card border p-3 rounded-3 mb-3 bg-white text-center d-none">
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill small mb-2 mx-auto">
                            <i class="bx bx-qr-scan me-1"></i> Pembayaran QRIS Resmi IFGS
                        </span>

                        <div class="p-2 bg-white rounded border d-inline-block shadow-sm mx-auto mb-2" style="max-width: 240px;">
                            <img id="m2_qris_image" src="{{ asset('img/qris-ifgs.svg') }}" alt="QRIS IFGS" class="img-fluid rounded" style="max-height: 220px; width: 100%; object-fit: contain;">
                        </div>

                        <div class="fw-bold text-dark small" id="m2_qris_holder">Indo Fitness Gym Sport</div>
                        <small class="text-muted font-monospace d-block mb-2" id="m2_qris_account">NMID: ID1024300928172</small>

                        <div class="alert alert-light border small text-muted text-start mb-0" style="font-size: 0.72rem; line-height: 1.4;">
                            <i class="bx bx-info-circle text-primary me-1"></i> Buka aplikasi mobile banking (BCA, Mandiri, BRI) atau e-wallet (GoPay, OVO, Dana) Anda, lalu scan kode QRIS di atas.
                        </div>
                    </div>

                    <!-- Petunjuk Ringkas -->
                    <div class="p-2 rounded-3 bg-light border small text-muted" style="font-size: 0.72rem; line-height: 1.4;">
                        <div class="fw-bold text-dark mb-1">Petunjuk Pembayaran:</div>
                        <ol class="mb-0 ps-3">
                            <li>Lakukan transfer sesuai nominal tepat yang tertera di atas.</li>
                            <li>Simpan resi / struk atau tangkapan layar (screenshot) bukti transfer Anda.</li>
                            <li>Klik tombol di bawah untuk melanjutkan ke pengunggahan bukti pembayaran.</li>
                        </ol>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-sm btn-light border" onclick="backToStep1()">
                        <i class="bx bx-chevron-left me-1"></i> Ganti Metode
                    </button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4" onclick="goToStep3()">
                        Upload Bukti Pembayaran <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: Upload Bukti Pembayaran           -->
    <!-- ========================================== -->
    <div class="modal fade" id="modalCheckoutStep3" tabindex="-1" aria-labelledby="modalCheckoutStep3Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalCheckoutStep3Label">
                        <i class="bx bx-upload text-primary me-1"></i> Upload Bukti Pembayaran
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formCheckoutMembership" action="{{ route('memberships.order') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="final_product_id">
                    <input type="hidden" name="payment_method_id" id="final_payment_method_id">
                    <input type="hidden" name="start_date" id="final_start_date">

                    <div class="modal-body p-3">
                        <!-- Stepper Indicator -->
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Metode</span>
                            </div>
                            <div style="width: 24px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-success text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;"><i class="bx bx-check"></i></span>
                                <span class="small text-muted" style="font-size: 0.72rem;">Bayar</span>
                            </div>
                            <div style="width: 24px; height: 2px; background-color: #198754;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge rounded-circle bg-primary text-white" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem;">3</span>
                                <span class="small fw-bold text-primary" style="font-size: 0.72rem;">Bukti</span>
                            </div>
                        </div>

                        <!-- Ringkasan Singkat Pesanan -->
                        <div class="p-2 rounded bg-light border mb-3 small d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.68rem;">Pesanan:</span>
                                <strong class="text-dark" id="m3_product_name">-</strong>
                                <span class="text-muted"> &bull; </span>
                                <span class="text-muted" id="m3_payment_name">-</span>
                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block" style="font-size: 0.68rem;">Total:</span>
                                <strong class="text-primary" id="m3_product_price">Rp 0</strong>
                            </div>
                        </div>

                        <!-- Dropzone Upload Area -->
                        <div class="mb-3">
                            <label for="payment_proof" class="form-label small fw-bold text-dark">Upload Bukti Transfer / Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" id="payment_proof" name="payment_proof" accept="image/*" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Format: JPG, PNG, WEBP (Maksimal 2MB).</small>
                            <label class="form-label small fw-bold text-dark mb-1">
                                Upload Bukti Transfer / Pembayaran <span class="text-danger">*</span>
                            </label>

                            <input type="file" id="order_payment_proof" name="payment_proof"
                                accept="image/jpeg,image/png,image/jpg,image/webp" class="d-none" required>

                            <div id="dropzoneArea" class="border border-2 border-dashed rounded-3 p-3 text-center bg-white cursor-pointer"
                                style="border-color: #cbd5e1 !important; cursor: pointer; transition: all 0.2s;">
                                
                                <div id="uploadPlaceholder">
                                    <div class="mx-auto bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mb-2"
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
                                    <button type="button" id="btnChangeProof" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 mt-1" style="font-size: 0.72rem;">
                                        <i class="bx bx-refresh me-1"></i> Ganti Foto
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Format yang didukung: JPG, PNG, WEBP.</small>
                        </div>

                        <!-- 4. Catatan Opsional -->
                        <!-- Catatan Tambahan (Opsional) -->
                        <div class="mb-2">
                            <label for="order_notes" class="form-label small fw-bold text-dark">Catatan (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="order_notes" name="notes" placeholder="Contoh: Transfer via BCA atas nama John">
                            <label for="order_notes" class="form-label small fw-bold text-dark mb-1">Catatan Tambahan (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="order_notes" name="notes"
                                placeholder="Contoh: Transfer atas nama John via BCA">
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                    <div class="modal-footer border-top py-2 px-3 d-flex align-items-center justify-content-between">
                        <button type="button" class="btn btn-sm btn-light border" onclick="backToStep2()">
                            <i class="bx bx-chevron-left me-1"></i> Kembali
                        </button>
                        <button type="submit" id="btnSubmitOrder" class="btn btn-sm btn-primary rounded-pill px-4">
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
        .pg-method-card:hover {
            border-color: #dc2626 !important;
            background-color: rgba(220, 38, 38, 0.02);
        }
        .pg-method-card.selected {
            border-color: #dc2626 !important;
            background-color: rgba(220, 38, 38, 0.05);
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.15);
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
            background-color: #f8fafc;
        }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderModal = new bootstrap.Modal(document.getElementById('modalOrderMembership'));
        const productIdInput = document.getElementById('order_product_id');
        const productNameText = document.getElementById('order_product_name');
        const productPriceText = document.getElementById('order_product_price');
        const productDurationText = document.getElementById('order_product_duration');
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Modal Instances
            const modalEl1 = document.getElementById('modalCheckoutStep1');
            const modalEl2 = document.getElementById('modalCheckoutStep2');
            const modalEl3 = document.getElementById('modalCheckoutStep3');

        const paymentSelect = document.getElementById('order_payment_method');
        const rekeningBox = document.getElementById('rekeningDetailBox');
        const pmBankName = document.getElementById('pmBankName');
        const pmAccountNumber = document.getElementById('pmAccountNumber');
        const pmAccountHolder = document.getElementById('pmAccountHolder');
            const modalStep1 = new bootstrap.Modal(modalEl1);
            const modalStep2 = new bootstrap.Modal(modalEl2);
            const modalStep3 = new bootstrap.Modal(modalEl3);

        // Klik Tombol Pilih Paket
        document.querySelectorAll('.btn-pilih-paket').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = this.getAttribute('data-price');
                const duration = this.getAttribute('data-duration');
            // State Data
            let selectedProduct = null;
            let selectedPaymentMethod = null;

                productIdInput.value = id;
                productNameText.textContent = name;
                productPriceText.textContent = price;
                productDurationText.textContent = 'Masa berlaku: ' + duration + ' hari';
            // Transisi Antar Modal Tanpa Glitch
            function switchModal(fromModalEl, toModalInstance) {
                const fromInstance = bootstrap.Modal.getInstance(fromModalEl);
                if (fromInstance) {
                    fromInstance.hide();
                    fromModalEl.addEventListener('hidden.bs.modal', function onHidden() {
                        fromModalEl.removeEventListener('hidden.bs.modal', onHidden);
                        toModalInstance.show();
                    });
                } else {
                    toModalInstance.show();
                }
            }

                orderModal.show();
            window.goToStep2 = function () {
                if (!selectedPaymentMethod) return;

                // Isi Data di Modal 2
                document.getElementById('m2_product_price').textContent = selectedProduct.price;
                document.getElementById('m2_product_badge').textContent = selectedProduct.name + ' (' + selectedProduct.duration + ' Hari)';

                const bankBox = document.getElementById('m2_bank_box');
                const qrisBox = document.getElementById('m2_qris_box');

                if (selectedPaymentMethod.type === 'qris') {
                    bankBox.classList.add('d-none');
                    qrisBox.classList.remove('d-none');

                    if (selectedPaymentMethod.qr) {
                        document.getElementById('m2_qris_image').src = selectedPaymentMethod.qr;
                    }
                    document.getElementById('m2_qris_holder').textContent = selectedPaymentMethod.holder || 'Indo Fitness Gym Sport';
                    document.getElementById('m2_qris_account').textContent = selectedPaymentMethod.account || 'NMID: ID1024300928172';
                } else {
                    qrisBox.classList.add('d-none');
                    bankBox.classList.remove('d-none');

                    document.getElementById('m2_bank_name').textContent = selectedPaymentMethod.name;
                    document.getElementById('m2_account_number').textContent = selectedPaymentMethod.account || '-';
                    document.getElementById('m2_account_holder').textContent = selectedPaymentMethod.holder || 'IFGS Gym';
                }

                switchModal(modalEl1, modalStep2);
            };

            window.backToStep1 = function () {
                switchModal(modalEl2, modalStep1);
            };

            window.goToStep3 = function () {
                // Isi Ringkasan di Modal 3
                document.getElementById('m3_product_name').textContent = selectedProduct.name;
                document.getElementById('m3_payment_name').textContent = selectedPaymentMethod.name;
                document.getElementById('m3_product_price').textContent = selectedProduct.price;

                document.getElementById('final_product_id').value = selectedProduct.id;
                document.getElementById('final_payment_method_id').value = selectedPaymentMethod.id;
                document.getElementById('final_start_date').value = new Date().toISOString().split('T')[0];

                switchModal(modalEl2, modalStep3);
            };

            window.backToStep2 = function () {
                switchModal(modalEl3, modalStep2);
            };

            // Salin Nomor Rekening
            window.copyAccountNumber = function () {
                const accountNum = document.getElementById('m2_account_number').textContent.trim();
                if (!accountNum || accountNum === '-') return;

                const copyBtn = document.getElementById('btnCopyAccount');
                const originalHtml = copyBtn.innerHTML;

                navigator.clipboard.writeText(accountNum).then(() => {
                    copyBtn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                    copyBtn.classList.remove('btn-outline-primary');
                    copyBtn.classList.add('btn-success');
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHtml;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-primary');
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
            document.querySelectorAll('.btn-pilih-paket').forEach(function (button) {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const price = this.getAttribute('data-price');
                    const duration = this.getAttribute('data-duration');
                    const desc = this.getAttribute('data-description') || '';

                    selectedProduct = { id, name, price, duration, desc };

                    // Isi data modal 1
                    document.getElementById('m1_product_name').textContent = name;
                    document.getElementById('m1_product_price').textContent = price;
                    document.getElementById('m1_product_duration').textContent = 'Durasi: ' + duration + ' Hari';
                    document.getElementById('m1_product_desc').textContent = desc;

                    // Reset seleksi metode
                    selectedPaymentMethod = null;
                    document.querySelectorAll('.pg-method-card').forEach(c => c.classList.remove('selected'));
                    document.getElementById('btnStep1Next').disabled = true;

                    // Reset form & upload preview
                    document.getElementById('formCheckoutMembership').reset();
                    document.getElementById('uploadPlaceholder').classList.remove('d-none');
                    document.getElementById('uploadPreviewBox').classList.add('d-none');
                    document.getElementById('previewImage').src = '';

                    modalStep1.show();
                });
            });
        });

        // Ubah Metode Pembayaran -> Tampilkan Nomor Rekening
        paymentSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const account = selected.getAttribute('data-account');
            const holder = selected.getAttribute('data-holder');
            const name = selected.getAttribute('data-name');
            // Klik Pilihan Metode Pembayaran (PG Style)
            document.querySelectorAll('.pg-method-card').forEach(function (card) {
                card.addEventListener('click', function () {
                    document.querySelectorAll('.pg-method-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');

            if (account || holder) {
                rekeningBox.classList.remove('d-none');
                pmBankName.textContent = name || 'Rekening Pembayaran';
                pmAccountNumber.textContent = account || '-';
                pmAccountHolder.textContent = holder || 'IFGS Gym';
            } else {
                rekeningBox.classList.add('d-none');
            }
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

            dropzoneArea.addEventListener('click', function (e) {
                if (e.target !== btnChangeProof) {
                    fileInput.click();
                }
            });

            btnChangeProof.addEventListener('click', function (e) {
                e.stopPropagation();
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
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
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        previewFileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                        uploadPlaceholder.classList.add('d-none');
                        uploadPreviewBox.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Submit Form via AJAX + SweetAlert2
            const form = document.getElementById('formCheckoutMembership');
            form.addEventListener('submit', function (e) {
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
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim...';

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                    }
                    return data;
                })
                .then(data => {
                    modalStep3.hide();

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
                            confirmButton: 'btn btn-primary rounded-pill px-4'
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
                        text: error.message || 'Terjadi kendala saat mengirim bukti pembayaran. Silakan coba lagi.',
                        confirmButtonColor: '#dc2626'
                    });
                });
            });

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
                        confirmButton: 'btn btn-primary rounded-pill px-4'
                    },
                    buttonsStyling: false
                });
            @endif
        });
    });
</script>
    </script>
@endpush


