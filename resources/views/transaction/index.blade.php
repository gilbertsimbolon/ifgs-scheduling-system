@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-2 mb-0">
                    <span class="text-muted fw-light">Transaksi /</span> Riwayat Transaksi
                </h5>
                <p class="text-muted mb-0">Daftar seluruh riwayat transaksi POS dan pembayaran paket gym.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pos.index') }}" class="btn btn-primary">
                    <i class="bx bx-cart me-1"></i> Transaksi Baru (POS)
                </a>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Total Transaksi</span>
                                <h4 class="mb-0 fw-bold">{{ number_format($metrics['total_count']) }}</h4>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-receipt fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Total Pendapatan</span>
                                <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</h4>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-wallet fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Transaksi Hari Ini</span>
                                <h4 class="mb-0 fw-bold">{{ number_format($metrics['today_count']) }}</h4>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-calendar-event fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Omset Hari Ini</span>
                                <h4 class="mb-0 fw-bold text-primary">Rp {{ number_format($metrics['today_revenue'], 0, ',', '.') }}</h4>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-trending-up fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body border-bottom">
                <form action="{{ route('transactions.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Pencarian</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" name="search" class="form-control" 
                                    placeholder="No. Invoice / Nama Member..." 
                                    value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Metode Bayar</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">Semua Metode</option>
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" {{ request('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                        {{ $pm->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bx bx-filter-alt me-1"></i> Filter
                            </button>
                            @if (request()->hasAny(['search', 'payment_method_id', 'date_from', 'date_to', 'status']))
                                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                    <i class="bx bx-reset"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Riwayat Transaksi -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>No. Invoice</th>
                            <th>Tanggal & Waktu</th>
                            <th>Member</th>
                            <th>Layanan</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Kasir</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($transactions as $index => $trx)
                            <tr>
                                <td>{{ $transactions->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-bold text-primary">{{ $trx->invoice_number }}</span>
                                </td>
                                <td>
                                    <span class="d-block">{{ $trx->created_at->format('d M Y') }}</span>
                                    <small class="text-muted">{{ $trx->created_at->format('H:i') }} WITA</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $trx->member?->user?->name ?? '-' }}</span>
                                    <small class="text-muted d-block">{{ $trx->member?->member_code ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $trx->items->first()?->product_name ?? '-' }}</span>
                                    @if ($trx->items->count() > 1)
                                        <small class="badge bg-label-secondary">+{{ $trx->items->count() - 1 }} lainnya</small>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">{{ $trx->formatted_total_amount }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ $trx->paymentMethod?->name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $trx->status_badge_class }}">{{ $trx->status_label }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $trx->user?->name ?? 'Sistem' }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-detail-trx"
                                            title="Detail Transaksi" data-bs-toggle="modal"
                                            data-bs-target="#modalDetailTransaksi"
                                            data-invoice="{{ $trx->invoice_number }}"
                                            data-date="{{ $trx->created_at->format('d M Y, H:i') }}"
                                            data-member-name="{{ $trx->member?->user?->name ?? '-' }}"
                                            data-member-code="{{ $trx->member?->member_code ?? '-' }}"
                                            data-cashier="{{ $trx->user?->name ?? 'Sistem' }}"
                                            data-payment="{{ $trx->paymentMethod?->name ?? '-' }}"
                                            data-total="{{ $trx->formatted_total_amount }}"
                                            data-paid="{{ $trx->formatted_paid_amount }}"
                                            data-change="{{ $trx->formatted_change_amount }}"
                                            data-status="{{ $trx->status_label }}"
                                            data-status-badge="{{ $trx->status_badge_class }}"
                                            data-service-name="{{ $trx->items->first()?->product_name ?? '-' }}"
                                            data-service-price="{{ $trx->items->first()?->formatted_price ?? '-' }}"
                                            data-membership-valid="{{ $trx->membership ? ($trx->membership->start_date->format('d M Y') . ' s/d ' . $trx->membership->end_date->format('d M Y')) : '-' }}"
                                            data-notes="{{ $trx->notes ?? '-' }}"
                                            data-receipt-url="{{ route('pos.receipt', $trx) }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Print Struk -->
                                        <a href="{{ route('pos.receipt', $trx) }}" target="_blank" 
                                            class="btn btn-sm btn-icon btn-outline-primary" title="Cetak Struk">
                                            <i class="bx bx-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-receipt fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'payment_method_id', 'date_from', 'date_to']))
                                                Tidak ada transaksi yang cocok dengan filter pencarian
                                            @else
                                                Belum ada riwayat transaksi
                                            @endif
                                        </h6>
                                        <p class="text-muted small mb-3">Buat transaksi pertama melalui menu POS Kasir.</p>
                                        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-cart me-1"></i> Buka POS Kasir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-3 border-top gap-2">
                <small class="text-muted">
                    @if ($transactions->total() > 0)
                        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} data transaksi
                    @else
                        Tidak ada data
                    @endif
                </small>
                @if ($transactions->hasPages())
                    {{ $transactions->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <div class="modal fade" id="modalDetailTransaksi" tabindex="-1" aria-labelledby="modalDetailTransaksiLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-3">
                    <h6 class="modal-title text-white fw-bold" id="modalDetailTransaksiLabel">
                        <i class="bx bx-receipt me-1"></i> Detail Invoice: <span id="modalTrxInvoice">-</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Top Info Summary -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Waktu Transaksi:</span>
                            <span class="fw-bold" id="modalTrxDate">-</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Petugas Kasir:</span>
                            <span class="fw-bold" id="modalTrxCashier">-</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Status:</span>
                            <span id="modalTrxStatus" class="badge bg-label-success">-</span>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted small d-block">Metode Bayar:</span>
                            <span class="badge bg-label-info fw-bold" id="modalTrxPayment">-</span>
                        </div>
                    </div>

                    <!-- Member Card -->
                    <div class="card border mb-3 bg-light">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small d-block">Member Terdaftar:</span>
                                    <h6 class="mb-0 fw-bold text-dark" id="modalTrxMemberName">-</h6>
                                </div>
                                <div>
                                    <span class="badge bg-white text-dark border fw-bold" id="modalTrxMemberCode">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm m-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Layanan Gym</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold" id="modalTrxServiceName">-</td>
                                    <td class="text-end" id="modalTrxServicePrice">-</td>
                                    <td class="text-center">1</td>
                                    <td class="text-end fw-bold" id="modalTrxItemSubtotal">-</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light border-top">
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL TAGIHAN:</th>
                                    <th class="text-end fs-6 text-primary" id="modalTrxTotalAmount">-</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end text-muted small">Nominal Uang Diterima:</th>
                                    <th class="text-end text-muted small" id="modalTrxPaidAmount">-</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end text-muted small">Kembalian:</th>
                                    <th class="text-end text-success" id="modalTrxChangeAmount">-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Membership Hak Akses -->
                    <div class="alert alert-light border small p-3 mb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-id-card me-1 text-primary"></i> 
                                <strong class="text-dark">Penerbitan Hak Akses Membership:</strong>
                            </div>
                            <span class="badge bg-label-success">Aktif</span>
                        </div>
                        <div class="mt-1 text-muted">
                            Periode Masa Berlaku: <strong class="text-dark" id="modalTrxMembershipValid">-</strong>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="small text-muted">
                        <strong>Catatan:</strong> <span id="modalTrxNotes">-</span>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="modalTrxReceiptBtn" target="_blank" class="btn btn-primary">
                        <i class="bx bx-printer me-1"></i> Cetak Struk
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const detailButtons = document.querySelectorAll('.btn-detail-trx');

            detailButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const invoice = this.dataset.invoice;
                    const date = this.dataset.date;
                    const memberName = this.dataset.memberName;
                    const memberCode = this.dataset.memberCode;
                    const cashier = this.dataset.cashier;
                    const payment = this.dataset.payment;
                    const total = this.dataset.total;
                    const paid = this.dataset.paid;
                    const change = this.dataset.change;
                    const status = this.dataset.status;
                    const statusBadge = this.dataset.statusBadge;
                    const serviceName = this.dataset.serviceName;
                    const servicePrice = this.dataset.servicePrice;
                    const membershipValid = this.dataset.membershipValid;
                    const notes = this.dataset.notes;
                    const receiptUrl = this.dataset.receiptUrl;

                    document.getElementById('modalTrxInvoice').textContent = invoice;
                    document.getElementById('modalTrxDate').textContent = date;
                    document.getElementById('modalTrxCashier').textContent = cashier;
                    document.getElementById('modalTrxPayment').textContent = payment;

                    const statusEl = document.getElementById('modalTrxStatus');
                    statusEl.textContent = status;
                    statusEl.className = 'badge ' + statusBadge;

                    document.getElementById('modalTrxMemberName').textContent = memberName;
                    document.getElementById('modalTrxMemberCode').textContent = memberCode;
                    document.getElementById('modalTrxServiceName').textContent = serviceName;
                    document.getElementById('modalTrxServicePrice').textContent = servicePrice;
                    document.getElementById('modalTrxItemSubtotal').textContent = servicePrice;
                    document.getElementById('modalTrxTotalAmount').textContent = total;
                    document.getElementById('modalTrxPaidAmount').textContent = paid;
                    document.getElementById('modalTrxChangeAmount').textContent = change;
                    document.getElementById('modalTrxMembershipValid').textContent = membershipValid;
                    document.getElementById('modalTrxNotes').textContent = notes;

                    const receiptBtn = document.getElementById('modalTrxReceiptBtn');
                    if (receiptBtn && receiptUrl) {
                        receiptBtn.href = receiptUrl;
                    }
                });
            });
        });
    </script>
@endpush

