@extends('layouts.app')

@section('title', 'Membership')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Membership
                </h5>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahMembership">
                    <i class="bx bx-plus me-1"></i> Tambah Membership
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Global Validation Alert (if outside modal) -->
        @if ($errors->any() && !old('_modal'))
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

        <!-- Filter & Table Card -->
        <div class="card">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('memberships.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="search">Cari Transaksi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Cari nama member, kode, atau paket..." value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="product_id">Filter Paket Layanan</label>
                        <select name="product_id" id="product_id" class="form-select">
                            <option value="">Semua Paket Layanan</option>
                            @foreach ($allProducts as $product)
                                <option value="{{ $product->id }}"
                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'product_id', 'status']))
                            <a href="{{ route('memberships.index') }}" class="btn btn-outline-secondary"
                                title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Transaksi Membership -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Member</th>
                            <th>Paket Layanan</th>
                            <th>Mulai</th>
                            <th>Akhir</th>
                            <th>Biaya</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($memberships as $membership)
                            <tr>
                                <td>{{ $memberships->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($membership->member?->user?->name ?? 'M', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-heading text-truncate" style="max-width: 200px;">
                                                {{ $membership->member?->user?->name ?? '-' }}
                                            </span>
                                            <small class="text-muted font-monospace">
                                                {{ $membership->member?->member_code ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ $membership->product?->name ?? '-' }}</span>
                                        <small
                                            class="text-muted">{{ $membership->product?->duration_formatted ?? '-' }}</small>
                                    </div>
                                </td>
                                <td>
                                    {{ $membership->start_date ? $membership->start_date->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $membership->end_date ? $membership->end_date->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $membership->formatted_price }}
                                </td>
                                <td>
                                    {{ $membership->paymentMethod ? ucwords(str_replace('_', ' ', $membership->paymentMethod->name)) : 'Tunai' }}
                                </td>
                                <td>
                                    <span class="badge {{ $membership->status_badge_class }}">
                                        {{ $membership->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info"
                                            title="Detail Membership" data-bs-toggle="modal"
                                            data-bs-target="#modalDetailMembership"
                                            data-name="{{ $membership->member?->user?->name ?? '-' }}"
                                            data-code="{{ $membership->member?->member_code ?? '-' }}"
                                            data-product="{{ $membership->product?->name ?? '-' }}"
                                            data-duration="{{ $membership->product?->duration_formatted ?? '-' }}"
                                            data-start="{{ $membership->start_date ? $membership->start_date->format('d M Y') : '-' }}"
                                            data-end="{{ $membership->end_date ? $membership->end_date->format('d M Y') : '-' }}"
                                            data-price="{{ $membership->formatted_price }}"
                                            data-payment-method="{{ $membership->paymentMethod ? ucwords(str_replace('_', ' ', $membership->paymentMethod->name)) : 'Tunai' }}"
                                            data-status="{{ $membership->status_label }}"
                                            data-status-class="{{ $membership->status_badge_class }}"
                                            data-created="{{ $membership->created_at->format('d M Y, H:i') }}"
                                            data-cashier="{{ $membership->cashier_name }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Cancel Button -->
                                        @if ($membership->status === 'active')
                                            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary"
                                                title="Batalkan Membership" data-bs-toggle="modal"
                                                data-bs-target="#modalBatalMembership"
                                                data-action="{{ route('memberships.cancel', $membership) }}"
                                                data-member-name="{{ $membership->member?->user?->name ?? '-' }}"
                                                data-product="{{ $membership->product?->name ?? '-' }}">
                                                <i class="bx bx-x-circle"></i>
                                            </button>
                                        @endif

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus Membership" data-bs-toggle="modal"
                                            data-bs-target="#modalHapusMembership"
                                            data-action="{{ route('memberships.destroy', $membership) }}"
                                            data-member-name="{{ $membership->member?->user?->name ?? '-' }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-credit-card fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'product_id', 'status']))
                                                Tidak ada data membership yang ditemukan
                                            @else
                                                Belum ada data membership
                                            @endif
                                        </h6>
                                        <span class="text-muted">
                                            @if (request()->hasAny(['search', 'product_id', 'status']))
                                                Coba ubah kata kunci pencarian atau bersihkan filter.
                                            @else
                                                Data transaksi dan langganan membership akan ditampilkan di sini.
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">
                    @if ($memberships->total() > 0)
                        Menampilkan {{ $memberships->firstItem() }}–{{ $memberships->lastItem() }} dari
                        {{ $memberships->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </small>
                @if ($memberships->hasPages())
                    {{ $memberships->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('membership.modals.tambah')
    @include('membership.modals.detail')
    @include('membership.modals.batal')
    @include('membership.modals.hapus')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper calculate end date on Tambah Modal
            const selectProduct = document.getElementById('tambahProductId');
            const inputStartDate = document.getElementById('tambahStartDate');
            const inputEndDate = document.getElementById('tambahEndDate');
            const inputPrice = document.getElementById('tambahPrice');

            function updateCalculations() {
                if (!selectProduct) return;
                const selectedOption = selectProduct.options[selectProduct.selectedIndex];
                if (!selectedOption || !selectedOption.value) return;

                const price = selectedOption.getAttribute('data-price');
                const durationValue = parseInt(selectedOption.getAttribute('data-duration-value'), 10);
                const durationUnit = selectedOption.getAttribute('data-duration-unit');

                if (inputPrice && (!inputPrice.value || inputPrice.dataset.autoFilled === "true")) {
                    inputPrice.value = parseInt(price, 10) || '';
                    inputPrice.dataset.autoFilled = "true";
                }

                const startDateVal = inputStartDate ? inputStartDate.value : '';
                if (startDateVal && durationValue && durationUnit) {
                    const start = new Date(startDateVal);
                    if (!isNaN(start.getTime())) {
                        const calculatedEnd = new Date(start);
                        if (durationUnit === 'day') {
                            const daysToAdd = Math.max(0, durationValue - 1);
                            calculatedEnd.setDate(calculatedEnd.getDate() + daysToAdd);
                        } else if (durationUnit === 'week') {
                            calculatedEnd.setDate(calculatedEnd.getDate() + (durationValue * 7));
                        } else if (durationUnit === 'month') {
                            calculatedEnd.setMonth(calculatedEnd.getMonth() + durationValue);
                        } else if (durationUnit === 'year') {
                            calculatedEnd.setFullYear(calculatedEnd.getFullYear() + durationValue);
                        }

                        const yyyy = calculatedEnd.getFullYear();
                        const mm = String(calculatedEnd.getMonth() + 1).padStart(2, '0');
                        const dd = String(calculatedEnd.getDate()).padStart(2, '0');

                        if (inputEndDate && (!inputEndDate.value || inputEndDate.dataset.autoFilled === "true")) {
                            inputEndDate.value = `${yyyy}-${mm}-${dd}`;
                            inputEndDate.dataset.autoFilled = "true";
                        }
                    }
                }
            }

            if (selectProduct) {
                selectProduct.addEventListener('change', function() {
                    if (inputPrice) inputPrice.dataset.autoFilled = "true";
                    if (inputEndDate) inputEndDate.dataset.autoFilled = "true";
                    updateCalculations();
                });
            }

            if (inputStartDate) {
                inputStartDate.addEventListener('change', function() {
                    if (inputEndDate) inputEndDate.dataset.autoFilled = "true";
                    updateCalculations();
                });
            }

            if (inputPrice) {
                inputPrice.addEventListener('input', function() {
                    this.dataset.autoFilled = "false";
                });
            }

            if (inputEndDate) {
                inputEndDate.addEventListener('input', function() {
                    this.dataset.autoFilled = "false";
                });
            }

            // Modal Detail Populate
            const modalDetail = document.getElementById('modalDetailMembership');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const name = btn.getAttribute('data-name') || '-';
                    const code = btn.getAttribute('data-code') || '-';
                    const product = btn.getAttribute('data-product') || '-';
                    const duration = btn.getAttribute('data-duration') || '-';
                    const start = btn.getAttribute('data-start') || '-';
                    const end = btn.getAttribute('data-end') || '-';
                    const price = btn.getAttribute('data-price') || '-';
                    const paymentMethod = btn.getAttribute('data-payment-method') || '-';
                    const status = btn.getAttribute('data-status') || '-';
                    const statusClass = btn.getAttribute('data-status-class') || 'bg-label-secondary';
                    const created = btn.getAttribute('data-created') || '-';
                    const cashier = btn.getAttribute('data-cashier') || '-';

                    document.getElementById('detailMembershipMemberName').textContent = name;
                    document.getElementById('detailMembershipMemberCode').textContent = code;
                    document.getElementById('detailMembershipProductName').textContent = product;
                    document.getElementById('detailMembershipDuration').textContent = duration;
                    document.getElementById('detailMembershipStartDate').textContent = start;
                    document.getElementById('detailMembershipEndDate').textContent = end;
                    document.getElementById('detailMembershipPrice').textContent = price;
                    document.getElementById('detailMembershipCreatedAt').textContent = created;

                    const cashierEl = document.getElementById('detailMembershipCashier');
                    if (cashierEl) cashierEl.textContent = cashier;

                    const pmEl = document.getElementById('detailMembershipPaymentMethod');
                    if (pmEl) {
                        pmEl.textContent = paymentMethod;
                    }

                    const avatarEl = document.getElementById('detailMembershipAvatar');
                    if (avatarEl) {
                        avatarEl.textContent = name.substring(0, 2).toUpperCase();
                    }

                    const badgeEl = document.getElementById('detailMembershipStatusBadge');
                    if (badgeEl) {
                        badgeEl.textContent = status;
                        badgeEl.className = 'badge ' + statusClass;
                    }
                });
            }

            // Modal Batal Populate
            const modalBatal = document.getElementById('modalBatalMembership');
            if (modalBatal) {
                modalBatal.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const memberName = btn.getAttribute('data-member-name') || '-';
                    const product = btn.getAttribute('data-product') || '-';

                    const form = document.getElementById('formBatalMembership');
                    if (form) form.action = action;

                    document.getElementById('batalMembershipMemberName').textContent = memberName;
                    document.getElementById('batalMembershipProduct').textContent = product;
                });
            }

            // Modal Hapus Populate
            const modalHapus = document.getElementById('modalHapusMembership');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const memberName = btn.getAttribute('data-member-name') || '-';

                    const form = document.getElementById('formHapusMembership');
                    if (form) form.action = action;

                    document.getElementById('hapusMembershipMemberName').textContent = memberName;
                });
            }

            // Auto reopen modal on validation failure
            @if ($errors->any() && old('_modal') === 'create_membership')
                const modalTambahEl = document.getElementById('modalTambahMembership');
                if (modalTambahEl && typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modalTambahEl).show();
                }
            @endif
        });
    </script>
@endpush
