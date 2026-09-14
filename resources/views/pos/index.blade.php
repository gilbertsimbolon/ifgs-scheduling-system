@extends('layouts.app')

@section('title', 'POS Kasir - Transaksi Baru')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-2 mb-0">
                    <span class="text-muted fw-light">Transaksi /</span> POS Kasir
                </h5>
                <p class="text-muted mb-0">Kasir penjualan paket layanan dan penerbitan membership IFGS.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('memberships.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-id-card me-1"></i> Data Membership
                </a>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-history me-1"></i> Riwayat Transaksi
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-info-circle fs-4 me-2"></i>
                    <div>{{ session('info') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <h6 class="alert-heading fw-bold mb-1">
                    <i class="bx bx-error me-1"></i> Terjadi kesalahan input transaksi:
                </h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Form Transaksi POS -->
        <form action="{{ route('pos.store') }}" method="POST" id="posForm">
            @csrf

            <div class="row g-4">
                <!-- Sisi Kiri: Pilihan Member, Paket Layanan & Periode -->
                <div class="col-lg-8">
                    <!-- 1. Pilih Member -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom">
                            <h6 class="card-title m-0 fw-bold text-primary">
                                <i class="bx bx-user me-2"></i> 1. Pilih Member
                            </h6>
                            <span class="badge bg-label-primary">Wajib</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="mb-3">
                                <label for="memberSelect" class="form-label fw-semibold">Pilih Member Terdaftar <span class="text-danger">*</span></label>
                                <select name="member_id" id="memberSelect" class="form-select @error('member_id') is-invalid @enderror" required>
                                    <option value="">-- Cari / Pilih Member --</option>
                                    @foreach ($members as $member)
                                        @php
                                            $activeMs = $member->activeMembership();
                                            $membershipStatusText = $activeMs 
                                                ? "Aktif: {$activeMs->product?->name} (s/d {$activeMs->end_date->format('d M Y')})"
                                                : "Belum ada membership aktif";
                                            $suggestedStart = $activeMs && $activeMs->end_date->isFuture()
                                                ? $activeMs->end_date->copy()->addDay()->format('Y-m-d')
                                                : now()->format('Y-m-d');
                                        @endphp
                                        <option value="{{ $member->id }}"
                                            data-code="{{ $member->member_code }}"
                                            data-phone="{{ $member->phone ?? '-' }}"
                                            data-has-active="{{ $activeMs ? '1' : '0' }}"
                                            data-active-info="{{ $membershipStatusText }}"
                                            data-suggested-start="{{ $suggestedStart }}"
                                            {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->user?->name }} ({{ $member->member_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Info Member Box -->
                            <div id="memberInfoBox" class="p-3 bg-light rounded d-none">
                                <div class="row g-2 small">
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block">Kode Member:</span>
                                        <span class="fw-bold" id="infoMemberCode">-</span>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block">No. WhatsApp / HP:</span>
                                        <span class="fw-bold" id="infoMemberPhone">-</span>
                                    </div>
                                    <div class="col-sm-4">
                                        <span class="text-muted d-block">Status Saat Ini:</span>
                                        <span id="infoMemberStatus" class="badge bg-label-secondary">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pilih Paket Layanan / Produk -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom">
                            <h6 class="card-title m-0 fw-bold text-primary">
                                <i class="bx bx-package me-2"></i> 2. Pilih Paket Layanan Gym
                            </h6>
                            <span class="badge bg-label-primary">Wajib</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3" id="packageCardsContainer">
                                @forelse ($products as $product)
                                    <div class="col-md-6">
                                        <label class="card h-100 border package-option-card cursor-pointer p-3 position-relative"
                                            for="product_{{ $product->id }}"
                                            style="cursor: pointer; transition: all 0.2s ease;">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input product-radio" type="radio" 
                                                        name="product_id" 
                                                        id="product_{{ $product->id }}" 
                                                        value="{{ $product->id }}"
                                                        data-name="{{ $product->name }}"
                                                        data-price="{{ (float) $product->price }}"
                                                        data-formatted-price="{{ $product->formatted_price }}"
                                                        data-duration-formatted="{{ $product->duration_formatted }}"
                                                        data-duration-value="{{ $product->duration_value }}"
                                                        data-duration-unit="{{ $product->duration_unit }}"
                                                        {{ old('product_id') == $product->id ? 'checked' : '' }}
                                                        required>
                                                    <label class="form-check-label fw-bold text-dark fs-6" for="product_{{ $product->id }}">
                                                        {{ $product->name }}
                                                    </label>
                                                </div>
                                                <span class="badge bg-label-info">{{ $product->duration_formatted }}</span>
                                            </div>
                                            <div class="d-flex align-items-baseline justify-content-between mt-auto pt-2 border-top">
                                                <span class="text-muted small">Biaya Paket:</span>
                                                <span class="fw-bold fs-5 text-primary">{{ $product->formatted_price }}</span>
                                            </div>
                                            @if ($product->description)
                                                <small class="text-muted mt-1 d-block text-truncate">{{ $product->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4">
                                        <p class="text-muted mb-0">Belum ada paket layanan aktif. Silakan tambahkan di Master Paket Layanan.</p>
                                    </div>
                                @endforelse
                            </div>
                            @error('product_id')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 3. Periode Layanan & Catatan -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom">
                            <h6 class="card-title m-0 fw-bold text-primary">
                                <i class="bx bx-calendar me-2"></i> 3. Periode Layanan & Catatan
                            </h6>
                            <span class="badge bg-label-primary">Otomatis</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="startDateInput" class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                        id="startDateInput" 
                                        name="start_date" 
                                        value="{{ old('start_date', now()->format('Y-m-d')) }}" 
                                        required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Awal berlakunya hak akses gym bagi member.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="endDateInput" class="form-label fw-semibold">Tanggal Berakhir</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                            id="endDateInput" 
                                            name="end_date" 
                                            value="{{ old('end_date') }}">
                                        <button class="btn btn-outline-secondary" type="button" id="btnAutoEndDate" title="Hitung Ulang">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                    </div>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Dihitung otomatis berdasarkan durasi paket terpilih.</small>
                                </div>
                                <div class="col-12">
                                    <label for="notesInput" class="form-label fw-semibold">Catatan Transaksi (Opsional)</label>
                                    <textarea class="form-control" id="notesInput" name="notes" rows="2" 
                                        placeholder="Tambahkan catatan khusus bila ada...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Ringkasan Pembayaran & Kasir -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 10;">
                        <div class="card-header bg-primary text-white py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="card-title m-0 fw-bold text-white">
                                    <i class="bx bx-receipt me-1"></i> Ringkasan Transaksi
                                </h6>
                                <span class="badge bg-white text-primary fw-semibold">{{ now()->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <!-- Info Kasir -->
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom small">
                                <span class="text-muted">Petugas Kasir:</span>
                                <span class="fw-bold">{{ auth()->user()->name ?? 'Kasir' }}</span>
                            </div>

                            <!-- Detail Item Terpilih -->
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="text-muted small mb-1">Paket Layanan:</div>
                                <div class="fw-bold fs-6 text-dark" id="summaryProductName">Belum memilih paket</div>
                                <div class="small text-muted" id="summaryProductDuration">-</div>
                            </div>

                            <!-- Total Tagihan -->
                            <div class="d-flex align-items-baseline justify-content-between py-2 border-bottom mb-3">
                                <span class="fw-bold text-muted">TOTAL BIAYA</span>
                                <span class="fw-bolder fs-3 text-primary" id="summaryTotalAmount">Rp 0</span>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    @foreach ($paymentMethods as $pm)
                                        <div class="col-12">
                                            <label class="form-check-label border rounded p-2 d-flex align-items-center w-100 cursor-pointer payment-method-card"
                                                for="pm_{{ $pm->id }}" style="cursor: pointer;">
                                                <input class="form-check-input me-2 m-0 payment-radio" type="radio" 
                                                    name="payment_method_id" 
                                                    id="pm_{{ $pm->id }}" 
                                                    value="{{ $pm->id }}"
                                                    data-code="{{ $pm->code }}"
                                                    {{ (old('payment_method_id') == $pm->id || (empty(old('payment_method_id')) && $pm->code === 'cash')) ? 'checked' : '' }}
                                                    required>
                                                <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                                    <span class="fw-semibold">
                                                        @if ($pm->code === 'cash')
                                                            <i class="bx bx-money me-1 text-success"></i>
                                                        @elseif ($pm->code === 'bank_transfer')
                                                            <i class="bx bx-transfer me-1 text-primary"></i>
                                                        @elseif ($pm->code === 'qris')
                                                            <i class="bx bx-qr me-1 text-danger"></i>
                                                        @else
                                                            <i class="bx bx-credit-card me-1 text-info"></i>
                                                        @endif
                                                        {{ $pm->name }}
                                                    </span>
                                                    <span class="badge bg-label-secondary small">{{ strtoupper($pm->code) }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_method_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Section Uang Tunai & Kembalian (Khusus Tunai) -->
                            <div id="cashPaymentSection" class="mb-3">
                                <label for="paidAmountInput" class="form-label fw-semibold">Uang Dibayarkan (Rp)</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control form-control-lg fw-bold text-end @error('paid_amount') is-invalid @enderror" 
                                        id="paidAmountInput" 
                                        name="paid_amount" 
                                        placeholder="0" 
                                        min="0"
                                        value="{{ old('paid_amount') }}">
                                </div>
                                @error('paid_amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <!-- Quick Cash Presets -->
                                <div class="d-flex flex-wrap gap-1 mb-2" id="quickCashButtons">
                                    <button type="button" class="btn btn-xs btn-outline-primary quick-cash" data-mode="exact">Uang Pas</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary quick-cash" data-val="50000">50.000</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary quick-cash" data-val="100000">100.000</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary quick-cash" data-val="200000">200.000</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary quick-cash" data-val="500000">500.000</button>
                                </div>

                                <!-- Box Kembalian -->
                                <div class="p-2 border rounded bg-white d-flex align-items-center justify-content-between">
                                    <span class="text-muted small fw-semibold">Kembalian:</span>
                                    <span class="fw-bold fs-6 text-success" id="changeAmountDisplay">Rp 0</span>
                                </div>
                            </div>

                            <!-- Non-Cash Notice -->
                            <div id="nonCashNotice" class="alert alert-light border small py-2 mb-3 d-none">
                                <i class="bx bx-info-circle me-1 text-info"></i> Pembayaran non-tunai diverifikasi sesuai nominal tagihan yang pas.
                            </div>

                            <!-- Tombol Bayar & Proses -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold" id="btnSubmitPos">
                                <i class="bx bx-check-circle me-1"></i> Bayar & Terbitkan Membership
                            </button>
                            <small class="text-muted d-block text-center mt-2">
                                Transaksi akan dicatat dan status membership member otomatis diperbarui.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabel Transaksi Terkini Hari Ini -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom">
                <div>
                    <h6 class="card-title m-0 fw-bold">
                        <i class="bx bx-history me-1"></i> Transaksi Terakhir Hari Ini
                    </h6>
                    <small class="text-muted">Menampilkan 5 transaksi kasir terbaru yang diproses hari ini.</small>
                </div>
                <div class="badge bg-label-success fs-6">
                    Omset Hari Ini: Rp {{ number_format($todayStats['revenue'], 0, ',', '.') }} ({{ $todayStats['count'] }} Transaksi)
                </div>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Waktu</th>
                            <th>Member</th>
                            <th>Paket Layanan</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $trx)
                            <tr>
                                <td>
                                    <span class="fw-bold text-primary">{{ $trx->invoice_number }}</span>
                                </td>
                                <td>{{ $trx->created_at->format('H:i') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $trx->member?->user?->name ?? '-' }}</span>
                                    <small class="text-muted d-block">{{ $trx->member?->member_code ?? '-' }}</small>
                                </td>
                                <td>
                                    {{ $trx->membership?->product?->name ?? ($trx->items->first()?->product_name ?? '-') }}
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $trx->paymentMethod?->name ?? '-' }}</span>
                                </td>
                                <td class="fw-bold text-dark">{{ $trx->formatted_total_amount }}</td>
                                <td>
                                    <span class="badge {{ $trx->status_badge_class }}">{{ $trx->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('pos.receipt', $trx) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Cetak Struk">
                                        <i class="bx bx-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Belum ada transaksi yang diproses hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Struk Pembayaran Terakhir (Pop-up Sukses) -->
    @if ($lastTransaction)
        <div class="modal fade" id="modalStrukSukses" tabindex="-1" aria-labelledby="modalStrukSuksesLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white py-3">
                        <h6 class="modal-title text-white fw-bold" id="modalStrukSuksesLabel">
                            <i class="bx bx-check-circle me-1"></i> Transaksi Berhasil Diproses!
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bx bx-badge-check text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $lastTransaction->formatted_total_amount }}</h5>
                        <p class="text-muted mb-3">{{ $lastTransaction->invoice_number }}</p>

                        <div class="bg-light p-3 rounded text-start mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Member:</span>
                                <span class="fw-bold">{{ $lastTransaction->member?->user?->name }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Layanan:</span>
                                <span class="fw-bold">{{ $lastTransaction->items->first()?->product_name }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Masa Berlaku:</span>
                                <span class="fw-bold">{{ $lastTransaction->membership?->start_date?->format('d/m/Y') }} s/d {{ $lastTransaction->membership?->end_date?->format('d/m/Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Metode Pembayaran:</span>
                                <span class="fw-bold">{{ $lastTransaction->paymentMethod?->name }}</span>
                            </div>
                            @if ($lastTransaction->paymentMethod?->code === 'cash')
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Uang Diterima:</span>
                                    <span>{{ $lastTransaction->formatted_paid_amount }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Kembalian:</span>
                                    <span class="fw-bold text-success">{{ $lastTransaction->formatted_change_amount }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between py-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Selesai / Transaksi Baru
                        </button>
                        <a href="{{ route('pos.receipt', $lastTransaction) }}" target="_blank" class="btn btn-primary">
                            <i class="bx bx-printer me-1"></i> Cetak Struk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const memberSelect = document.getElementById('memberSelect');
            const memberInfoBox = document.getElementById('memberInfoBox');
            const infoMemberCode = document.getElementById('infoMemberCode');
            const infoMemberPhone = document.getElementById('infoMemberPhone');
            const infoMemberStatus = document.getElementById('infoMemberStatus');

            const productRadios = document.querySelectorAll('.product-radio');
            const packageCards = document.querySelectorAll('.package-option-card');
            const startDateInput = document.getElementById('startDateInput');
            const endDateInput = document.getElementById('endDateInput');
            const btnAutoEndDate = document.getElementById('btnAutoEndDate');

            const summaryProductName = document.getElementById('summaryProductName');
            const summaryProductDuration = document.getElementById('summaryProductDuration');
            const summaryTotalAmount = document.getElementById('summaryTotalAmount');

            const paymentRadios = document.querySelectorAll('.payment-radio');
            const cashPaymentSection = document.getElementById('cashPaymentSection');
            const nonCashNotice = document.getElementById('nonCashNotice');
            const paidAmountInput = document.getElementById('paidAmountInput');
            const changeAmountDisplay = document.getElementById('changeAmountDisplay');
            const quickCashButtons = document.querySelectorAll('.quick-cash');

            let currentProductPrice = 0;

            // 1. Member Selection Change
            function onMemberChange() {
                const selectedOpt = memberSelect.options[memberSelect.selectedIndex];
                if (!selectedOpt || !selectedOpt.value) {
                    memberInfoBox.classList.add('d-none');
                    return;
                }

                const code = selectedOpt.dataset.code || '-';
                const phone = selectedOpt.dataset.phone || '-';
                const hasActive = selectedOpt.dataset.hasActive === '1';
                const activeInfo = selectedOpt.dataset.activeInfo || '-';
                const suggestedStart = selectedOpt.dataset.suggestedStart;

                infoMemberCode.textContent = code;
                infoMemberPhone.textContent = phone;
                infoMemberStatus.textContent = activeInfo;
                infoMemberStatus.className = 'badge ' + (hasActive ? 'bg-label-success' : 'bg-label-secondary');

                memberInfoBox.classList.remove('d-none');

                if (suggestedStart && !startDateInput.dataset.manualEdited) {
                    startDateInput.value = suggestedStart;
                    calculateEndDate();
                }
            }

            if (memberSelect) {
                memberSelect.addEventListener('change', onMemberChange);
                if (memberSelect.value) onMemberChange();
            }

            // 2. Product Selection & End Date Calculation
            function getSelectedProduct() {
                for (const r of productRadios) {
                    if (r.checked) return r;
                }
                return null;
            }

            function updatePackageCardStyles() {
                packageCards.forEach(card => {
                    const radio = card.querySelector('.product-radio');
                    if (radio && radio.checked) {
                        card.classList.add('border-primary', 'bg-label-primary');
                    } else {
                        card.classList.remove('border-primary', 'bg-label-primary');
                    }
                });
            }

            function calculateEndDate() {
                const selectedProd = getSelectedProduct();
                if (!selectedProd) return;

                const durationValue = parseInt(selectedProd.dataset.durationValue, 10);
                const durationUnit = selectedProd.dataset.durationUnit;
                const startDateVal = startDateInput.value;

                if (!startDateVal || isNaN(durationValue)) return;

                const start = new Date(startDateVal);
                if (isNaN(start.getTime())) return;

                const end = new Date(start);
                if (durationUnit === 'day') {
                    end.setDate(end.getDate() + durationValue);
                } else if (durationUnit === 'week') {
                    end.setDate(end.getDate() + (durationValue * 7));
                } else if (durationUnit === 'month') {
                    end.setMonth(end.getMonth() + durationValue);
                } else if (durationUnit === 'year') {
                    end.setFullYear(end.getFullYear() + durationValue);
                }

                const yyyy = end.getFullYear();
                const mm = String(end.getMonth() + 1).padStart(2, '0');
                const dd = String(end.getDate()).padStart(2, '0');

                endDateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            function onProductChange() {
                const selectedProd = getSelectedProduct();
                if (!selectedProd) return;

                updatePackageCardStyles();

                const name = selectedProd.dataset.name;
                const formattedPrice = selectedProd.dataset.formattedPrice;
                const durationFormatted = selectedProd.dataset.durationFormatted;
                currentProductPrice = parseFloat(selectedProd.dataset.price) || 0;

                summaryProductName.textContent = name;
                summaryProductDuration.textContent = 'Durasi: ' + durationFormatted;
                summaryTotalAmount.textContent = formattedPrice;

                calculateEndDate();
                updateChange();
            }

            productRadios.forEach(r => {
                r.addEventListener('change', onProductChange);
            });

            if (startDateInput) {
                startDateInput.addEventListener('change', function() {
                    this.dataset.manualEdited = "true";
                    calculateEndDate();
                });
            }

            if (btnAutoEndDate) {
                btnAutoEndDate.addEventListener('click', calculateEndDate);
            }

            // 3. Payment Method & Cash Calculation
            function getSelectedPaymentCode() {
                for (const pm of paymentRadios) {
                    if (pm.checked) return pm.dataset.code;
                }
                return 'cash';
            }

            function onPaymentMethodChange() {
                const code = getSelectedPaymentCode();
                if (code === 'cash') {
                    cashPaymentSection.classList.remove('d-none');
                    nonCashNotice.classList.add('d-none');
                    paidAmountInput.required = true;
                } else {
                    cashPaymentSection.classList.add('d-none');
                    nonCashNotice.classList.remove('d-none');
                    paidAmountInput.required = false;
                    paidAmountInput.value = currentProductPrice;
                }
                updateChange();
            }

            paymentRadios.forEach(pm => {
                pm.addEventListener('change', onPaymentMethodChange);
            });

            function formatRupiah(num) {
                return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function updateChange() {
                const code = getSelectedPaymentCode();
                if (code !== 'cash') {
                    changeAmountDisplay.textContent = 'Rp 0';
                    return;
                }

                const paid = parseFloat(paidAmountInput.value) || 0;
                const change = paid - currentProductPrice;

                if (paid === 0 && currentProductPrice === 0) {
                    changeAmountDisplay.textContent = 'Rp 0';
                    changeAmountDisplay.className = 'fw-bold fs-6 text-muted';
                } else if (change >= 0) {
                    changeAmountDisplay.textContent = formatRupiah(change);
                    changeAmountDisplay.className = 'fw-bold fs-6 text-success';
                } else {
                    changeAmountDisplay.textContent = 'Kurang ' + formatRupiah(Math.abs(change));
                    changeAmountDisplay.className = 'fw-bold fs-6 text-danger';
                }
            }

            if (paidAmountInput) {
                paidAmountInput.addEventListener('input', updateChange);
            }

            // Quick cash buttons
            quickCashButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const mode = this.dataset.mode;
                    const val = parseFloat(this.dataset.val);

                    if (mode === 'exact') {
                        paidAmountInput.value = currentProductPrice;
                    } else if (!isNaN(val)) {
                        paidAmountInput.value = val;
                    }
                    updateChange();
                });
            });

            // Initial trigger
            onProductChange();
            onPaymentMethodChange();

            // Auto-open last transaction modal if available
            @if ($lastTransaction)
                const modalStrukEl = document.getElementById('modalStrukSukses');
                if (modalStrukEl && typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modalStrukEl).show();
                }
            @endif
        });
    </script>
@endpush

