@extends('layouts.app')

@section('title', 'Metode Pembayaran')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Master Data /</span> Metode Pembayaran
                    <span class="text-muted fw-light">Manajemen /</span> Metode Pembayaran
                </h5>
                <p class="text-muted mb-0">Kelola master data metode pembayaran untuk transaksi Membership & POS.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPaymentMethod">
                    <i class="bx bx-plus me-1"></i> Tambah Metode Pembayaran
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

        <!-- Global Validation Alert -->
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

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="text-muted fw-semibold">Total Metode</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2">{{ number_format($metrics['total']) }}</h4>
                                </div>
                                <small class="text-muted">Semua metode pembayaran</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-credit-card-front bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="text-muted fw-semibold">Metode Aktif</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-success">{{ number_format($metrics['active']) }}</h4>
                                </div>
                                <small class="text-success">Dapat digunakan transaksi</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-check-shield bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="text-muted fw-semibold">Non-Aktif</span>
                                <div class="d-flex align-items-center my-1">
                                    <h4 class="mb-0 me-2 text-secondary">{{ number_format($metrics['inactive']) }}</h4>
                                </div>
                                <small class="text-muted">Disembunyikan</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-secondary">
                                    <i class="bx bx-hide bx-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Table Card -->
        <div class="card">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('payment-methods.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="search">Cari Metode Pembayaran</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Ketik nama atau kode metode..."
                                value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('payment-methods.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel Metode Pembayaran -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Metode</th>
                            <th>Kode Unik</th>
                            <th>Status</th>
                            <th>Penggunaan</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($paymentMethods as $pm)
                            <tr>
                                <td>{{ $paymentMethods->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-info">
                                                <i class="bx bx-credit-card"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-heading">{{ $pm->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary font-monospace">{{ $pm->code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer toggle-status-switch"
                                                type="checkbox" role="switch" id="switchPm{{ $pm->id }}"
                                                data-action="{{ route('payment-methods.toggle-status', $pm) }}"
                                                title="Klik untuk ubah status"
                                                {{ $pm->status === \App\Models\PaymentMethod::STATUS_ACTIVE ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label cursor-pointer mb-0" for="switchPm{{ $pm->id }}">
                                            @if ($pm->status === \App\Models\PaymentMethod::STATUS_ACTIVE)
                                                <span class="badge bg-label-success status-badge">Aktif</span>
                                            @else
                                                <span class="badge bg-label-secondary status-badge">Non-Aktif</span>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary font-monospace">
                                        {{ $pm->memberships_count }} transaksi
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                                            title="Edit Metode" data-bs-toggle="modal" data-bs-target="#modalEditPaymentMethod"
                                            data-action="{{ route('payment-methods.update', $pm) }}"
                                            data-name="{{ $pm->name }}"
                                            data-code="{{ $pm->code }}"
                                            data-status="{{ $pm->status }}">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus Metode" data-bs-toggle="modal" data-bs-target="#modalHapusPaymentMethod"
                                            data-action="{{ route('payment-methods.destroy', $pm) }}"
                                            data-name="{{ $pm->name }}"
                                            data-count="{{ $pm->memberships_count }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-credit-card-front fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'status']))
                                                Tidak ada metode pembayaran yang ditemukan
                                            @else
                                                Belum ada master metode pembayaran
                                            @endif
                                        </h6>
                                        <span class="text-muted">
                                            @if (request()->hasAny(['search', 'status']))
                                                Coba ubah kata kunci pencarian atau bersihkan filter.
                                            @else
                                                Klik tombol "Tambah Metode Pembayaran" untuk menambahkan data baru.
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
                    @if ($paymentMethods->total() > 0)
                        Menampilkan {{ $paymentMethods->firstItem() }}–{{ $paymentMethods->lastItem() }} dari {{ $paymentMethods->total() }} data
                    @else
                        Tidak ada data
                    @endif
                </small>
                @if ($paymentMethods->hasPages())
                    {{ $paymentMethods->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('payment_method.modals.tambah')
    @include('payment_method.modals.edit')
    @include('payment_method.modals.hapus')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal Edit: populate on show
            const modalEdit = document.getElementById('modalEditPaymentMethod');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const name = btn.getAttribute('data-name') || '';
                    const code = btn.getAttribute('data-code') || '';
                    const status = btn.getAttribute('data-status') || 'active';

                    const form = document.getElementById('formEditPaymentMethod');
                    if (form) form.action = action;

                    const actionInput = document.getElementById('editPaymentMethodActionInput');
                    if (actionInput) actionInput.value = action;

                    document.getElementById('editPaymentMethodName').value = name;
                    document.getElementById('editPaymentMethodCode').value = code;
                    document.getElementById('editPaymentMethodStatus').value = status;
                });
            }

            // Modal Hapus: populate on show
            const modalHapus = document.getElementById('modalHapusPaymentMethod');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const name = btn.getAttribute('data-name') || '';
                    const count = parseInt(btn.getAttribute('data-count') || '0', 10);

                    const form = document.getElementById('formHapusPaymentMethod');
                    if (form) form.action = action;

                    document.getElementById('hapusPaymentMethodName').textContent = name;

                    const warningEl = document.getElementById('hapusPaymentMethodWarning');
                    const subtextEl = document.getElementById('hapusPaymentMethodSubtext');
                    const submitBtn = document.getElementById('btnConfirmHapusPaymentMethod');

                    if (count > 0) {
                        warningEl.classList.remove('d-none');
                        subtextEl.classList.add('d-none');
                        submitBtn.disabled = true;
                    } else {
                        warningEl.classList.add('d-none');
                        subtextEl.classList.remove('d-none');
                        submitBtn.disabled = false;
                    }
                });
            }

            // Status Toggle Switch AJAX
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            document.querySelectorAll('.toggle-status-switch').forEach(function(switchEl) {
                switchEl.addEventListener('change', function() {
                    const currentSwitch = this;
                    const action = currentSwitch.getAttribute('data-action');
                    const badgeEl = currentSwitch.closest('td')?.querySelector('.status-badge');
                    const isChecked = currentSwitch.checked;

                    currentSwitch.disabled = true;

                    fetch(action, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Gagal memperbarui status');
                        return response.json();
                    })
                    .then(data => {
                        currentSwitch.disabled = false;
                        if (data.success) {
                            if (badgeEl) {
                                badgeEl.textContent = data.label;
                                badgeEl.className = 'badge status-badge ' + (data.status === 'active' ? 'bg-label-success' : 'bg-label-secondary');
                            }
                        } else {
                            currentSwitch.checked = !isChecked;
                        }
                    })
                    .catch(error => {
                        currentSwitch.disabled = false;
                        currentSwitch.checked = !isChecked;
                        alert('Terjadi kesalahan saat mengubah status metode pembayaran.');
                    });
                });
            });

            // Auto reopen modal on validation failure
            @if ($errors->any())
                @if (old('_modal') === 'create_payment_method')
                    const modalTambahEl = document.getElementById('modalTambahPaymentMethod');
                    if (modalTambahEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambahEl).show();
                    }
                @elseif (old('_modal') === 'edit_payment_method')
                    const prevAction = '{{ old('_action') }}';
                    const formEdit = document.getElementById('formEditPaymentMethod');
                    if (formEdit && prevAction) {
                        formEdit.action = prevAction;
                    }
                    const modalEditEl = document.getElementById('modalEditPaymentMethod');
                    if (modalEditEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalEditEl).show();
                    }
                @endif
            @endif
        });
    </script>
@endpush

