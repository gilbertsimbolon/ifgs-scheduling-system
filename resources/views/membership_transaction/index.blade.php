@extends('layouts.app')

@section('title', 'Transaksi Membership')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Transaksi Membership
                </h5>
                <p class="text-muted small mb-0">
                    Validasi pembayaran transfer member, periksa bukti transfer, setujui (ACC), atau tolak pesanan
                    membership.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('memberships.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-group me-1"></i> Data Membership
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bx bx-error me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible" role="alert">
                <h6 class="alert-heading fw-bold mb-1">Terjadi kesalahan input:</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-start border-warning border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Menunggu Validasi</span>
                                <h4 class="card-title mb-0 text-warning">{{ number_format($metrics['pending']) }}</h4>
                            </div>
                            <div class="avatar bg-light-warning rounded p-2">
                                <i class="bx bx-time-five fs-2 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-start border-success border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Transaksi Disetujui</span>
                                <h4 class="card-title mb-0 text-success">{{ number_format($metrics['approved']) }}</h4>
                            </div>
                            <div class="avatar bg-light-success rounded p-2">
                                <i class="bx bx-check-double fs-2 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-start border-danger border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Ditolak / Batal</span>
                                <h4 class="card-title mb-0 text-danger">{{ number_format($metrics['rejected']) }}</h4>
                            </div>
                            <div class="avatar bg-light-danger rounded p-2">
                                <i class="bx bx-x-circle fs-2 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-start border-primary border-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted fw-semibold d-block mb-1">Total Pendapatan</span>
                                <h4 class="card-title mb-0 text-primary">Rp
                                    {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</h4>
                            </div>
                            <div class="avatar bg-light-primary rounded p-2">
                                <i class="bx bx-wallet fs-2 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card shadow-sm">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('membership-transactions.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Cari Transaksi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Cari nama member, kode, invoice, atau paket..."
                                value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach ($statuses as $stat)
                                <option value="{{ $stat }}" {{ request('status') == $stat ? 'selected' : '' }}>
                                    {{ ucfirst($stat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="payment_method_id">Metode Pembayaran</label>
                        <select name="payment_method_id" id="payment_method_id" class="form-select">
                            <option value="">Semua Metode</option>
                            @foreach ($paymentMethods as $pm)
                                <option value="{{ $pm->id }}"
                                    {{ request('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                    {{ $pm->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        <a href="{{ route('membership-transactions.index') }}" class="btn btn-outline-secondary"
                            title="Reset Filter">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>No. Invoice</th>
                            <th>Tanggal Pesanan</th>
                            <th>Pelanggan / Member</th>
                            <th>Paket Layanan</th>
                            <th>Nominal</th>
                            <th>Metode Bayar</th>
                            <th class="text-center">Bukti Bayar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 120px;">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($memberships as $index => $item)
                            @php
                                $proofUrl = $item->payment_proof_url;
                                $memberName = $item->member?->user?->name ?? 'Member Dihapus';
                                $memberCode = $item->member?->member_code ?? '-';
                                $memberEmail = $item->member?->user?->email ?? '-';
                                $memberPhone = $item->member?->phone_number ?? '-';
                                $productName = $item->product?->name ?? 'Paket Dihapus';
                                $durationText = $item->product?->duration_formatted ?? '-';
                                $priceFormatted = 'Rp ' . number_format($item->price, 0, ',', '.');
                                $isCash = $item->isCashPayment();
                                $methodName = $item->paymentMethod?->name ?? ($isCash ? 'Tunai' : 'Transfer');
                                $invoiceNumber = $item->transaction?->invoice_number ?? 'MEM-' . $item->id;
                                $cashierName = $item->transaction?->user?->name ?? '-';
                                $rejectionReason = $item->transaction?->rejection_reason ?? '';
                                $createdAtFormatted = $item->created_at
                                    ? $item->created_at->translatedFormat('d M Y, H:i')
                                    : '-';
                                $periodFormatted =
                                    ($item->start_date ? $item->start_date->format('d/m/Y') : '-') .
                                    ' s/d ' .
                                    ($item->end_date ? $item->end_date->format('d/m/Y') : '-');
                            @endphp
                            <tr>
                                <td class="text-center fw-semibold text-muted">
                                    {{ $memberships->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $invoiceNumber }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small text-nowrap">{{ $createdAtFormatted }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                {{ strtoupper(substr($memberName, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $memberName }}</div>
                                            <div class="small text-muted">{{ $memberCode }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $productName }}</div>
                                    <div class="small text-muted"><i class="bx bx-time me-1"></i>{{ $durationText }}
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">{{ $priceFormatted }}</span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $methodName }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($isCash)
                                        <span class="badge bg-label-secondary">
                                            <i class="bx bx-money me-1"></i>Tunai
                                        </span>
                                    @elseif ($proofUrl)
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-preview-proof"
                                            data-bs-toggle="modal" data-bs-target="#modalPreviewBuktiTf"
                                            data-invoice="{{ $invoiceNumber }}" data-proof-url="{{ $proofUrl }}"
                                            title="Lihat Bukti Transfer">
                                            <i class="bx bx-image me-1"></i> Lihat Bukti
                                        </button>
                                    @else
                                        <span class="badge bg-label-warning">Belum Ada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $item->status_badge_class }}">
                                        {{ $item->status_label }}
                                    </span>
                                    @if ($item->status === \App\Models\Membership::STATUS_REJECTED && $rejectionReason)
                                        <div class="small text-danger mt-1" title="{{ $rejectionReason }}">
                                            <i class="bx bx-info-circle"></i>
                                            {{ \Illuminate\Support\Str::limit($rejectionReason, 20) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-detail-tx"
                                            data-bs-toggle="modal" data-bs-target="#modalDetailTransaction"
                                            data-id="{{ $item->id }}" data-member-name="{{ $memberName }}"
                                            data-member-code="{{ $memberCode }}" data-email="{{ $memberEmail }}"
                                            data-phone="{{ $memberPhone }}" data-product="{{ $productName }}"
                                            data-duration="{{ $durationText }}" data-price="{{ $priceFormatted }}"
                                            data-method="{{ $methodName }}" data-is-cash="{{ $isCash ? '1' : '0' }}"
                                            data-invoice="{{ $invoiceNumber }}" data-created="{{ $createdAtFormatted }}"
                                            data-period="{{ $periodFormatted }}" data-cashier="{{ $cashierName }}"
                                            data-status="{{ $item->status }}"
                                            data-status-label="{{ $item->status_label }}"
                                            data-status-class="{{ $item->status_badge_class }}"
                                            data-proof-url="{{ $proofUrl ?? '' }}" data-reason="{{ $rejectionReason }}"
                                            data-acc-action="{{ route('membership-transactions.approve', $item) }}"
                                            data-tolak-action="{{ route('membership-transactions.reject', $item) }}"
                                            title="Lihat Detail & Bukti">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        @if ($item->status === \App\Models\Membership::STATUS_PENDING)
                                            <!-- ACC Button -->
                                            <button type="button" class="btn btn-sm btn-icon btn-success btn-acc-tx"
                                                data-bs-toggle="modal" data-bs-target="#modalAccTransaction"
                                                data-action="{{ route('membership-transactions.approve', $item) }}"
                                                data-member-name="{{ $memberName }}"
                                                data-product="{{ $productName }}" title="Setujui (ACC) Transaksi">
                                                <i class="bx bx-check"></i>
                                            </button>

                                            <!-- Tolak Button -->
                                            <button type="button" class="btn btn-sm btn-icon btn-danger btn-tolak-tx"
                                                data-bs-toggle="modal" data-bs-target="#modalTolakTransaction"
                                                data-action="{{ route('membership-transactions.reject', $item) }}"
                                                data-member-name="{{ $memberName }}"
                                                data-product="{{ $productName }}" title="Tolak Transaksi">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bx bx-receipt fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data transaksi membership ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($memberships->hasPages())
                <div class="card-footer d-flex justify-content-between align-items-center py-3">
                    <span class="text-muted small">
                        Menampilkan {{ $memberships->firstItem() }} sampai {{ $memberships->lastItem() }} dari
                        {{ $memberships->total() }} transaksi
                    </span>
                    <div>
                        {{ $memberships->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    @include('membership_transaction.modals.acc')
    @include('membership_transaction.modals.tolak')
    @include('membership_transaction.modals.bukti_tf')
    @include('membership_transaction.modals.detail')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview Bukti Transfer Modal
            const modalPreviewBukti = document.getElementById('modalPreviewBuktiTf');
            if (modalPreviewBukti) {
                modalPreviewBukti.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const invoice = btn.getAttribute('data-invoice') || '';
                    const proofUrl = btn.getAttribute('data-proof-url') || '';

                    document.getElementById('previewBuktiTfInvoice').textContent = invoice;
                    document.getElementById('previewBuktiTfImg').src = proofUrl;
                    document.getElementById('previewBuktiTfLink').href = proofUrl;
                });
            }

            // ACC Modal
            const modalAcc = document.getElementById('modalAccTransaction');
            if (modalAcc) {
                modalAcc.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const memberName = btn.getAttribute('data-member-name') || '-';
                    const product = btn.getAttribute('data-product') || '-';

                    const form = document.getElementById('formAccTransaction');
                    if (form) form.action = action;

                    document.getElementById('accTransactionMemberName').textContent = memberName;
                    document.getElementById('accTransactionProduct').textContent = product;
                });
            }

            // Tolak Modal
            const modalTolak = document.getElementById('modalTolakTransaction');
            if (modalTolak) {
                modalTolak.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const memberName = btn.getAttribute('data-member-name') || '-';
                    const product = btn.getAttribute('data-product') || '-';

                    const form = document.getElementById('formTolakTransaction');
                    if (form) {
                        form.action = action;
                        const reasonInput = document.getElementById('tolakTransactionReason');
                        if (reasonInput) reasonInput.value = '';
                    }

                    document.getElementById('tolakTransactionMemberName').textContent = memberName;
                    document.getElementById('tolakTransactionProduct').textContent = product;
                });
            }

            // Detail Modal
            let currentDetailAccAction = '';
            let currentDetailTolakAction = '';
            let currentDetailMemberName = '';
            let currentDetailProduct = '';

            const modalDetail = document.getElementById('modalDetailTransaction');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const name = btn.getAttribute('data-member-name') || '-';
                    const code = btn.getAttribute('data-member-code') || '-';
                    const email = btn.getAttribute('data-email') || '-';
                    const phone = btn.getAttribute('data-phone') || '-';
                    const product = btn.getAttribute('data-product') || '-';
                    const duration = btn.getAttribute('data-duration') || '-';
                    const price = btn.getAttribute('data-price') || '-';
                    const method = btn.getAttribute('data-method') || '-';
                    const isCash = btn.getAttribute('data-is-cash') === '1';
                    const invoice = btn.getAttribute('data-invoice') || '-';
                    const created = btn.getAttribute('data-created') || '-';
                    const period = btn.getAttribute('data-period') || '-';
                    const cashier = btn.getAttribute('data-cashier') || '-';
                    const status = btn.getAttribute('data-status') || '';
                    const statusLabel = btn.getAttribute('data-status-label') || '-';
                    const statusClass = btn.getAttribute('data-status-class') || 'bg-label-secondary';
                    const proofUrl = btn.getAttribute('data-proof-url') || '';
                    const reason = btn.getAttribute('data-reason') || '';

                    currentDetailAccAction = btn.getAttribute('data-acc-action') || '';
                    currentDetailTolakAction = btn.getAttribute('data-tolak-action') || '';
                    currentDetailMemberName = name;
                    currentDetailProduct = product;

                    document.getElementById('detailTxAvatar').textContent = name.substring(0, 2)
                        .toUpperCase();
                    document.getElementById('detailTxMemberName').textContent = name;
                    document.getElementById('detailTxMemberCode').textContent = code;
                    document.getElementById('detailTxEmail').textContent = email;
                    document.getElementById('detailTxPhone').textContent = phone;

                    // WhatsApp link
                    const waLink = document.getElementById('detailTxWaLink');
                    if (phone && phone !== '-') {
                        const cleanPhone = phone.replace(/\D/g, '').replace(/^0/, '62');
                        waLink.href = 'https://wa.me/' + cleanPhone;
                        waLink.classList.remove('d-none');
                    } else {
                        waLink.classList.add('d-none');
                    }

                    document.getElementById('detailTxInvoice').textContent = invoice;
                    document.getElementById('detailTxProductName').textContent = product;
                    document.getElementById('detailTxDuration').textContent = duration;
                    document.getElementById('detailTxPrice').textContent = price;
                    document.getElementById('detailTxPaymentMethod').textContent = method;
                    document.getElementById('detailTxCreatedAt').textContent = created;
                    document.getElementById('detailTxPeriod').textContent = period;
                    document.getElementById('detailTxCashier').textContent = cashier;

                    // Status Badge
                    const badge = document.getElementById('detailTxStatusBadge');
                    badge.textContent = statusLabel;
                    badge.className = 'badge ' + statusClass;

                    // Bukti Transfer vs Catatan Tunai
                    const proofSection = document.getElementById('detailTxProofSection');
                    const cashNote = document.getElementById('detailTxCashNote');
                    const proofImg = document.getElementById('detailTxProofImg');
                    const noProof = document.getElementById('detailTxNoProof');
                    const zoomBtn = document.getElementById('detailTxProofZoomBtn');

                    if (isCash) {
                        // Jika tunai, sembunyikan upload bukti transfer dan tampilkan note tunai
                        if (proofSection) proofSection.classList.add('d-none');
                        if (cashNote) {
                            cashNote.classList.remove('d-none');
                            cashNote.classList.add('d-flex');
                        }
                    } else {
                        // Jika non-tunai (transfer/qris), tampilkan bukti transfer
                        if (cashNote) {
                            cashNote.classList.add('d-none');
                            cashNote.classList.remove('d-flex');
                        }
                        if (proofSection) proofSection.classList.remove('d-none');

                        if (proofUrl) {
                            proofImg.src = proofUrl;
                            proofImg.classList.remove('d-none');
                            proofImg.classList.add('d-block');
                            noProof.classList.add('d-none');
                            noProof.classList.remove('d-flex');
                            zoomBtn.classList.remove('d-none');
                            zoomBtn.onclick = function() {
                                const modalDetailInst = bootstrap.Modal.getInstance(modalDetail);
                                if (modalDetailInst) modalDetailInst.hide();

                                const previewModal = new bootstrap.Modal(document.getElementById(
                                    'modalPreviewBuktiTf'));
                                document.getElementById('previewBuktiTfInvoice').textContent = invoice;
                                document.getElementById('previewBuktiTfImg').src = proofUrl;
                                document.getElementById('previewBuktiTfLink').href = proofUrl;
                                previewModal.show();
                            };
                            proofImg.onclick = zoomBtn.onclick;
                        } else {
                            proofImg.classList.add('d-none');
                            proofImg.classList.remove('d-block');
                            noProof.classList.remove('d-none');
                            noProof.classList.add('d-flex');
                            zoomBtn.classList.add('d-none');
                        }
                    }

                    // Rejection reason row
                    const rejectRow = document.getElementById('detailTxRejectRow');
                    const rejectReason = document.getElementById('detailTxRejectReason');
                    if (status === 'rejected' && reason) {
                        rejectReason.textContent = reason;
                        rejectRow.classList.remove('d-none');
                    } else {
                        rejectRow.classList.add('d-none');
                    }

                    // Action buttons & pending alert
                    const actionBtns = document.getElementById('detailTxActionButtons');
                    const pendingAlert = document.getElementById('detailTxPendingAlert');
                    if (status === 'pending') {
                        actionBtns.classList.remove('d-none');
                        pendingAlert.classList.remove('d-none');
                    } else {
                        actionBtns.classList.add('d-none');
                        pendingAlert.classList.add('d-none');
                    }
                });

                // Detail Modal ACC button click
                document.getElementById('detailTxBtnAcc')?.addEventListener('click', function() {
                    const detailModalInst = bootstrap.Modal.getInstance(modalDetail);
                    if (detailModalInst) detailModalInst.hide();

                    const accModalEl = document.getElementById('modalAccTransaction');
                    const form = document.getElementById('formAccTransaction');
                    if (form) form.action = currentDetailAccAction;
                    document.getElementById('accTransactionMemberName').textContent =
                        currentDetailMemberName;
                    document.getElementById('accTransactionProduct').textContent = currentDetailProduct;

                    const accModal = new bootstrap.Modal(accModalEl);
                    accModal.show();
                });

                // Detail Modal Tolak button click
                document.getElementById('detailTxBtnTolak')?.addEventListener('click', function() {
                    const detailModalInst = bootstrap.Modal.getInstance(modalDetail);
                    if (detailModalInst) detailModalInst.hide();

                    const tolakModalEl = document.getElementById('modalTolakTransaction');
                    const form = document.getElementById('formTolakTransaction');
                    if (form) {
                        form.action = currentDetailTolakAction;
                        const reasonInput = document.getElementById('tolakTransactionReason');
                        if (reasonInput) reasonInput.value = '';
                    }
                    document.getElementById('tolakTransactionMemberName').textContent =
                        currentDetailMemberName;
                    document.getElementById('tolakTransactionProduct').textContent = currentDetailProduct;

                    const tolakModal = new bootstrap.Modal(tolakModalEl);
                    tolakModal.show();
                });
            }
        });
    </script>
@endpush
