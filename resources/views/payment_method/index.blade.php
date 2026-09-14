@extends('layouts.app')

@section('title', 'Metode Pembayaran')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Metode Pembayaran
                </h5>
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
                                placeholder="Ketik nama, kode, atau no rekening..."
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
                            <th>Informasi Pembayaran</th>
                            <th>Status</th>
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
                                            <span class="avatar-initial rounded {{ $pm->type === 'qris' ? 'bg-label-danger' : ($pm->type === 'bank_transfer' ? 'bg-label-primary' : ($pm->type === 'ewallet' ? 'bg-label-info' : 'bg-label-success')) }}">
                                                @if ($pm->type === 'qris')
                                                    <i class="bx bx-qr-scan"></i>
                                                @elseif ($pm->type === 'bank_transfer')
                                                    <i class="bx bx-building"></i>
                                                @elseif ($pm->type === 'ewallet')
                                                    <i class="bx bx-mobile-alt"></i>
                                                @else
                                                    <i class="bx bx-money"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-heading d-block">{{ $pm->name }}</span>
                                            <small class="text-muted">{{ $pm->type_label }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary font-monospace">{{ $pm->code }}</span>
                                </td>
                                <td>
                                    @if ($pm->type === \App\Models\PaymentMethod::TYPE_QRIS)
                                        @if ($pm->qr_image_url)
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#modalPreviewQris"
                                                data-name="{{ $pm->name }}"
                                                data-merchant="{{ $pm->account_name ?: 'Indo Fitness Gym Sport' }}"
                                                data-account="{{ $pm->account_number ?: 'NMID: ID1024300928172' }}"
                                                data-img="{{ $pm->qr_image_url }}">
                                                <i class="bx bx-qr-scan me-1"></i> Lihat QRIS
                                            </button>
                                        @else
                                            <span class="text-muted small">
                                                <i class="bx bx-image-alt me-1"></i> Belum ada QRIS
                                            </span>
                                        @endif
                                    @elseif ($pm->type === \App\Models\PaymentMethod::TYPE_BANK_TRANSFER || $pm->type === \App\Models\PaymentMethod::TYPE_EWALLET)
                                        <div>
                                            <span class="fw-semibold font-monospace text-heading">{{ $pm->account_number ?? '-' }}</span>
                                            @if ($pm->account_name)
                                                <small class="text-muted d-block">a.n. {{ $pm->account_name }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-label-secondary">
                                            <i class="bx bx-wallet me-1"></i> Bayar di Kasir
                                        </span>
                                    @endif
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
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                                            title="Edit Metode" data-bs-toggle="modal" data-bs-target="#modalEditPaymentMethod"
                                            data-action="{{ route('payment-methods.update', $pm) }}"
                                            data-name="{{ $pm->name }}"
                                            data-type="{{ $pm->type }}"
                                            data-account-no="{{ $pm->account_number }}"
                                            data-account-name="{{ $pm->account_name }}"
                                            data-qr-image="{{ $pm->qr_image_url }}"
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
    @include('payment_method.modals.preview_qris')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper function for Modal Tambah dynamic type switching
            const selectTambahType = document.getElementById('tambahPaymentMethodType');
            const containerTambahAccount = document.getElementById('tambahContainerAccount');
            const containerTambahQris = document.getElementById('tambahContainerQris');
            const containerTambahCash = document.getElementById('tambahContainerCash');
            const labelTambahAccountNo = document.getElementById('tambahAccountNoLabel');

            function syncTambahFields() {
                if (!selectTambahType) return;
                const val = selectTambahType.value;

                if (val === 'bank_transfer') {
                    if (containerTambahAccount) containerTambahAccount.classList.remove('d-none');
                    if (containerTambahQris) containerTambahQris.classList.add('d-none');
                    if (containerTambahCash) containerTambahCash.classList.add('d-none');
                    if (labelTambahAccountNo) labelTambahAccountNo.innerHTML = 'Nomor Rekening Bank <span class="text-danger">*</span>';
                } else if (val === 'ewallet') {
                    if (containerTambahAccount) containerTambahAccount.classList.remove('d-none');
                    if (containerTambahQris) containerTambahQris.classList.add('d-none');
                    if (containerTambahCash) containerTambahCash.classList.add('d-none');
                    if (labelTambahAccountNo) labelTambahAccountNo.innerHTML = 'Nomor HP / E-Wallet <span class="text-danger">*</span>';
                } else if (val === 'qris') {
                    if (containerTambahAccount) containerTambahAccount.classList.add('d-none');
                    if (containerTambahQris) containerTambahQris.classList.remove('d-none');
                    if (containerTambahCash) containerTambahCash.classList.add('d-none');
                } else { // cash
                    if (containerTambahAccount) containerTambahAccount.classList.add('d-none');
                    if (containerTambahQris) containerTambahQris.classList.add('d-none');
                    if (containerTambahCash) containerTambahCash.classList.remove('d-none');
                }
            }

            if (selectTambahType) {
                selectTambahType.addEventListener('change', syncTambahFields);
                syncTambahFields();
            }

            // Helper function for Modal Edit dynamic type switching
            const selectEditType = document.getElementById('editPaymentMethodType');
            const containerEditAccount = document.getElementById('editContainerAccount');
            const containerEditQris = document.getElementById('editContainerQris');
            const containerEditCash = document.getElementById('editContainerCash');
            const labelEditAccountNo = document.getElementById('editAccountNoLabel');

            function syncEditFields() {
                if (!selectEditType) return;
                const val = selectEditType.value;

                if (val === 'bank_transfer') {
                    if (containerEditAccount) containerEditAccount.classList.remove('d-none');
                    if (containerEditQris) containerEditQris.classList.add('d-none');
                    if (containerEditCash) containerEditCash.classList.add('d-none');
                    if (labelEditAccountNo) labelEditAccountNo.innerHTML = 'Nomor Rekening Bank <span class="text-danger">*</span>';
                } else if (val === 'ewallet') {
                    if (containerEditAccount) containerEditAccount.classList.remove('d-none');
                    if (containerEditQris) containerEditQris.classList.add('d-none');
                    if (containerEditCash) containerEditCash.classList.add('d-none');
                    if (labelEditAccountNo) labelEditAccountNo.innerHTML = 'Nomor HP / E-Wallet <span class="text-danger">*</span>';
                } else if (val === 'qris') {
                    if (containerEditAccount) containerEditAccount.classList.add('d-none');
                    if (containerEditQris) containerEditQris.classList.remove('d-none');
                    if (containerEditCash) containerEditCash.classList.add('d-none');
                } else { // cash
                    if (containerEditAccount) containerEditAccount.classList.add('d-none');
                    if (containerEditQris) containerEditQris.classList.add('d-none');
                    if (containerEditCash) containerEditCash.classList.remove('d-none');
                }
            }

            if (selectEditType) {
                selectEditType.addEventListener('change', syncEditFields);
            }

            // Modal Edit: populate on show
            const modalEdit = document.getElementById('modalEditPaymentMethod');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const name = btn.getAttribute('data-name') || '';
                    const type = btn.getAttribute('data-type') || 'cash';
                    const accountNo = btn.getAttribute('data-account-no') || '';
                    const accountName = btn.getAttribute('data-account-name') || '';
                    const qrImage = btn.getAttribute('data-qr-image') || '';
                    const status = btn.getAttribute('data-status') || 'active';

                    const form = document.getElementById('formEditPaymentMethod');
                    if (form) form.action = action;

                    const actionInput = document.getElementById('editPaymentMethodActionInput');
                    if (actionInput) actionInput.value = action;

                    const nameInput = document.getElementById('editPaymentMethodName');
                    if (nameInput) nameInput.value = name;

                    if (selectEditType) selectEditType.value = type;

                    const accNoInput = document.getElementById('editPaymentMethodAccountNo');
                    if (accNoInput) accNoInput.value = accountNo;

                    const accNameInput = document.getElementById('editPaymentMethodAccountName');
                    if (accNameInput) accNameInput.value = accountName;

                    const statusInput = document.getElementById('editPaymentMethodStatus');
                    if (statusInput) statusInput.value = status;

                    const wrapperCurrentQr = document.getElementById('editCurrentQrWrapper');
                    const imgCurrentQr = document.getElementById('editCurrentQrImage');
                    if (wrapperCurrentQr && imgCurrentQr) {
                        if (qrImage) {
                            imgCurrentQr.src = qrImage;
                            wrapperCurrentQr.classList.remove('d-none');
                        } else {
                            imgCurrentQr.src = '';
                            wrapperCurrentQr.classList.add('d-none');
                        }
                    }

                    syncEditFields();
                });
            }

            // Modal Preview QRIS: populate on show
            const modalPreviewQris = document.getElementById('modalPreviewQris');
            if (modalPreviewQris) {
                modalPreviewQris.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const name = btn.getAttribute('data-name') || 'QRIS';
                    const merchant = btn.getAttribute('data-merchant') || 'Indo Fitness Gym Sport';
                    const account = btn.getAttribute('data-account') || '';
                    const img = btn.getAttribute('data-img') || '';

                    const titleEl = document.getElementById('previewQrisTitle');
                    if (titleEl) titleEl.innerHTML = '<i class="bx bx-qr-scan me-1 text-primary"></i> Kode QRIS: ' + name;

                    const merchantEl = document.getElementById('previewQrisMerchantName');
                    if (merchantEl) merchantEl.textContent = merchant;

                    const accountEl = document.getElementById('previewQrisAccount');
                    if (accountEl) accountEl.textContent = account;

                    const imgEl = document.getElementById('previewQrisImage');
                    if (imgEl) imgEl.src = img;

                    const dlBtn = document.getElementById('previewQrisDownloadBtn');
                    if (dlBtn) dlBtn.href = img;
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

                    const nameEl = document.getElementById('hapusPaymentMethodName');
                    if (nameEl) nameEl.textContent = name;

                    const warningEl = document.getElementById('hapusPaymentMethodWarning');
                    const subtextEl = document.getElementById('hapusPaymentMethodSubtext');
                    const submitBtn = document.getElementById('btnConfirmHapusPaymentMethod');

                    if (count > 0) {
                        if (warningEl) warningEl.classList.remove('d-none');
                        if (subtextEl) subtextEl.classList.add('d-none');
                        if (submitBtn) submitBtn.disabled = true;
                    } else {
                        if (warningEl) warningEl.classList.add('d-none');
                        if (subtextEl) subtextEl.classList.remove('d-none');
                        if (submitBtn) submitBtn.disabled = false;
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
