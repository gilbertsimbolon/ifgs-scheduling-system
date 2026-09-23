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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark fs-6" id="modalOrderMembershipLabel">
                        <i class="bx bx-cart text-primary me-1"></i> Pemesanan Paket Gym
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('memberships.order') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="order_product_id">

                    <div class="modal-body p-3">
                        <!-- 1. Ringkasan Paket Terpilih -->
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">Paket Dipilih:</span>
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fw-bold text-dark mb-0" id="order_product_name">-</h6>
                                <span class="fw-bold text-primary fs-6" id="order_product_price">Rp 0</span>
                            </div>
                            <small class="text-muted" id="order_product_duration" style="font-size: 0.72rem;"></small>
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
                        </div>

                        <!-- Box Detail Rekening Tujuan Transfer -->
                        <div id="rekeningDetailBox" class="p-3 rounded-3 border bg-white mb-3 d-none">
                            <span class="text-muted small d-block mb-1" style="font-size: 0.7rem;">Transfer Pembayaran ke:</span>
                            <div class="fw-bold text-dark" id="pmBankName" style="font-size: 0.88rem;">-</div>
                            <div class="d-flex align-items-center justify-content-between my-1">
                                <span class="text-primary fw-bold font-monospace fs-6" id="pmAccountNumber">-</span>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Atas Nama: <strong class="text-dark" id="pmAccountHolder">-</strong></small>
                        </div>

                        <!-- 3. Upload Bukti Pembayaran -->
                        <div class="mb-3">
                            <label for="payment_proof" class="form-label small fw-bold text-dark">Upload Bukti Transfer / Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" id="payment_proof" name="payment_proof" accept="image/*" required>
                            <small class="text-muted" style="font-size: 0.7rem;">Format: JPG, PNG, WEBP (Maksimal 2MB).</small>
                        </div>

                        <!-- 4. Catatan Opsional -->
                        <div class="mb-2">
                            <label for="order_notes" class="form-label small fw-bold text-dark">Catatan (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="order_notes" name="notes" placeholder="Contoh: Transfer via BCA atas nama John">
                        </div>
                    </div>

                    <div class="modal-footer border-top py-2 px-3">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                            <i class="bx bx-send me-1"></i> Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const orderModal = new bootstrap.Modal(document.getElementById('modalOrderMembership'));
        const productIdInput = document.getElementById('order_product_id');
        const productNameText = document.getElementById('order_product_name');
        const productPriceText = document.getElementById('order_product_price');
        const productDurationText = document.getElementById('order_product_duration');

        const paymentSelect = document.getElementById('order_payment_method');
        const rekeningBox = document.getElementById('rekeningDetailBox');
        const pmBankName = document.getElementById('pmBankName');
        const pmAccountNumber = document.getElementById('pmAccountNumber');
        const pmAccountHolder = document.getElementById('pmAccountHolder');

        // Klik Tombol Pilih Paket
        document.querySelectorAll('.btn-pilih-paket').forEach(function (button) {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const price = this.getAttribute('data-price');
                const duration = this.getAttribute('data-duration');

                productIdInput.value = id;
                productNameText.textContent = name;
                productPriceText.textContent = price;
                productDurationText.textContent = 'Masa berlaku: ' + duration + ' hari';

                orderModal.show();
            });
        });

        // Ubah Metode Pembayaran -> Tampilkan Nomor Rekening
        paymentSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const account = selected.getAttribute('data-account');
            const holder = selected.getAttribute('data-holder');
            const name = selected.getAttribute('data-name');

            if (account || holder) {
                rekeningBox.classList.remove('d-none');
                pmBankName.textContent = name || 'Rekening Pembayaran';
                pmAccountNumber.textContent = account || '-';
                pmAccountHolder.textContent = holder || 'IFGS Gym';
            } else {
                rekeningBox.classList.add('d-none');
            }
        });
    });
</script>
@endpush

