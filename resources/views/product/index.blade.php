@extends('layouts.app')

@section('title', 'Paket Layanan Gym')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Paket Layanan
                </h5>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahProduct">
                    <i class="bx bx-plus me-1"></i> Tambah Paket
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

        <!-- Card Daftar Paket -->
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Paket</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th class="text-center">Total Member</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($products as $product)
                            <tr>
                                <td>{{ $products->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-heading">{{ $product->name }}</span>
                                        @if ($product->description)
                                            <small class="text-muted text-truncate" style="max-width: 320px;">
                                                {{ $product->description }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        <i class="bx bx-time me-1 small"></i>{{ $product->duration_formatted }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer toggle-product-switch"
                                                type="checkbox" role="switch" id="switchProduct{{ $product->id }}"
                                                data-action="{{ route('products.toggle-status', $product) }}"
                                                title="Klik untuk ubah status paket"
                                                {{ $product->status === \App\Models\Product::STATUS_ACTIVE ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label cursor-pointer mb-0"
                                            for="switchProduct{{ $product->id }}">
                                            @if ($product->status === \App\Models\Product::STATUS_ACTIVE)
                                                <span class="badge bg-label-success status-badge">Aktif</span>
                                            @else
                                                <span class="badge bg-label-secondary status-badge">Nonaktif</span>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('memberships.index', ['product_id' => $product->id]) }}"
                                        class="text-warning fw-semibold text-decoration-none"
                                        title="Lihat daftar member untuk {{ $product->name }}">
                                        {{ $product->memberships_count }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    @php
                                        $productPayload = [
                                            'id' => $product->id,
                                            'name' => $product->name,
                                            'description' => $product->description,
                                            'status' => $product->status,
                                            'status_label' => $product->status === \App\Models\Product::STATUS_ACTIVE ? 'Aktif' : 'Nonaktif',
                                            'durations' => $product->durations->map(fn($d) => [
                                                'id' => $d->id,
                                                'duration_value' => $d->duration_value,
                                                'duration_unit' => $d->duration_unit,
                                                'duration_unit_label' => $d->duration_unit_label,
                                                'duration_formatted' => $d->duration_formatted,
                                                'price' => (float) $d->price,
                                                'formatted_price' => $d->formatted_price,
                                            ]),
                                        ];
                                    @endphp
                                    <div class="d-inline-flex gap-1">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-info btn-detail-product"
                                            title="Detail Paket" data-bs-toggle="modal" data-bs-target="#modalDetailProduct"
                                            data-product="{{ json_encode($productPayload) }}">
                                            <i class="bx bx-show"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning btn-edit-product"
                                            title="Edit Paket" data-bs-toggle="modal" data-bs-target="#modalEditProduct"
                                            data-action="{{ route('products.update', $product) }}"
                                            data-product="{{ json_encode($productPayload) }}">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                                            title="Hapus Paket" data-bs-toggle="modal" data-bs-target="#modalHapusProduct"
                                            data-action="{{ route('products.destroy', $product) }}"
                                            data-name="{{ $product->name }}">
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
                                            <i class="bx bx-package fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Belum ada paket layanan</h6>
                                        <span class="text-muted">Klik tombol "Tambah Paket" untuk membuat paket membership
                                            gym baru.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">
                    @if ($products->total() > 0)
                        Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari
                        {{ $products->total() }} paket
                    @else
                        Tidak ada data
                    @endif
                </small>
                @if ($products->hasPages())
                    {{ $products->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('product.modals.detail')
    @include('product.modals.tambah')
    @include('product.modals.edit')
    @include('product.modals.hapus')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const durationUnits = [
                { value: 'day', label: 'Hari' },
                { value: 'week', label: 'Minggu' },
                { value: 'month', label: 'Bulan' },
                { value: 'year', label: 'Tahun' },
                { value: 'lifetime', label: 'Seumur Hidup' },
            ];

            let tambahRowCounter = 0;
            let editRowCounter = 0;

            function buildDurationRowHtml(prefix, index, data = {}) {
                const val = (data.duration_value !== undefined && data.duration_value !== null) ? data.duration_value : 1;
                const unit = data.duration_unit || 'month';
                const price = (data.price !== undefined && data.price !== null) ? data.price : '';
                const id = data.id || '';
                const isLifetime = unit === 'lifetime';

                let unitOptions = '';
                durationUnits.forEach(u => {
                    const selected = u.value === unit ? 'selected' : '';
                    unitOptions += `<option value="${u.value}" ${selected}>${u.label}</option>`;
                });

                const idInput = id ? `<input type="hidden" name="durations[${index}][id]" value="${id}">` : '';

                return `
                    <tr class="duration-row align-middle" data-index="${index}" style="border-bottom: 1px solid var(--bs-border-color);">
                        ${idInput}
                        <td class="ps-1 py-2">
                            <input type="number" min="0" max="365"
                                class="form-control form-control-sm duration-value-input"
                                name="durations[${index}][duration_value]"
                                value="${isLifetime ? 0 : val}"
                                ${isLifetime ? 'readonly tabindex="-1"' : 'required'}>
                        </td>
                        <td class="py-2">
                            <select class="form-select form-select-sm duration-unit-select"
                                name="durations[${index}][duration_unit]" required>
                                ${unitOptions}
                            </select>
                        </td>
                        <td class="py-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted">Rp</span>
                                <input type="number" step="1000" min="0"
                                    class="form-control form-control-sm duration-price-input"
                                    name="durations[${index}][price]"
                                    value="${price}" placeholder="150000" required>
                            </div>
                        </td>
                        <td class="text-center pe-1 py-2">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-remove-duration"
                                title="Hapus Pilihan">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }

            function attachRowListeners(row) {
                const unitSelect = row.querySelector('.duration-unit-select');
                const valInput = row.querySelector('.duration-value-input');
                const removeBtn = row.querySelector('.btn-remove-duration');

                if (unitSelect && valInput) {
                    unitSelect.addEventListener('change', function() {
                        if (this.value === 'lifetime') {
                            valInput.value = 0;
                            valInput.readOnly = true;
                            valInput.setAttribute('tabindex', '-1');
                        } else {
                            valInput.readOnly = false;
                            valInput.removeAttribute('tabindex');
                            if (parseInt(valInput.value, 10) === 0 || !valInput.value) {
                                valInput.value = 1;
                            }
                        }
                    });
                }

                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        const tbody = row.closest('tbody');
                        const rows = tbody.querySelectorAll('.duration-row');
                        if (rows.length <= 1) {
                            alert('Minimal harus ada 1 pilihan durasi dan harga.');
                            return;
                        }
                        row.remove();
                    });
                }
            }

            // MODAL TAMBAH: Container & Add Button
            const tambahContainer = document.getElementById('tambahDurationsContainer');
            const btnTambahRow = document.getElementById('btnTambahDurationRow');

            function addRowToTambah(data = {}) {
                if (!tambahContainer) return;
                const html = buildDurationRowHtml('tambah', tambahRowCounter++, data);
                tambahContainer.insertAdjacentHTML('beforeend', html);
                const newRow = tambahContainer.lastElementChild;
                attachRowListeners(newRow);
            }

            if (btnTambahRow) {
                btnTambahRow.addEventListener('click', function() {
                    addRowToTambah({ duration_value: 1, duration_unit: 'month', price: '' });
                });
            }

            const modalTambahEl = document.getElementById('modalTambahProduct');
            if (modalTambahEl) {
                modalTambahEl.addEventListener('show.bs.modal', function() {
                    // Pastikan ada minimal 1 baris default saat modal dibuka jika belum ada
                    if (tambahContainer && tambahContainer.querySelectorAll('.duration-row').length === 0) {
                        tambahRowCounter = 0;
                        addRowToTambah({ duration_value: 1, duration_unit: 'month', price: '' });
                    }
                });
            }

            // Inisialisasi awal modal Tambah jika ada old input
            @php
                $oldTambahDurations = (old('_modal') === 'create_product' && is_array(old('durations'))) ? old('durations') : null;
            @endphp
            @if ($oldTambahDurations)
                const oldTambahData = {!! json_encode($oldTambahDurations) !!};
                if (tambahContainer) {
                    tambahContainer.innerHTML = '';
                    tambahRowCounter = 0;
                    Object.values(oldTambahData).forEach(item => {
                        addRowToTambah(item);
                    });
                }
            @else
                if (tambahContainer && tambahContainer.querySelectorAll('.duration-row').length === 0) {
                    addRowToTambah({ duration_value: 1, duration_unit: 'month', price: '' });
                }
            @endif

            // MODAL EDIT: Container & Add Button
            const editContainer = document.getElementById('editDurationsContainer');
            const btnEditRow = document.getElementById('btnEditAddDurationRow');

            function addRowToEdit(data = {}) {
                if (!editContainer) return;
                const html = buildDurationRowHtml('edit', editRowCounter++, data);
                editContainer.insertAdjacentHTML('beforeend', html);
                const newRow = editContainer.lastElementChild;
                attachRowListeners(newRow);
            }

            if (btnEditRow) {
                btnEditRow.addEventListener('click', function() {
                    addRowToEdit({ duration_value: 1, duration_unit: 'month', price: '' });
                });
            }

            const modalEdit = document.getElementById('modalEditProduct');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const rawProduct = btn.getAttribute('data-product');

                    const form = document.getElementById('formEditProduct');
                    if (form) form.action = action;

                    const actionInput = document.getElementById('editProductActionInput');
                    if (actionInput) actionInput.value = action;

                    if (!rawProduct) return;
                    try {
                        const product = JSON.parse(rawProduct);
                        document.getElementById('editProductName').value = product.name || '';
                        document.getElementById('editProductDesc').value = product.description || '';
                        document.getElementById('editProductStatus').value = product.status || 'active';

                        if (editContainer) {
                            editContainer.innerHTML = '';
                            editRowCounter = 0;
                            if (product.durations && product.durations.length > 0) {
                                product.durations.forEach(d => {
                                    addRowToEdit({
                                        id: d.id,
                                        duration_value: d.duration_value,
                                        duration_unit: d.duration_unit,
                                        price: d.price
                                    });
                                });
                            } else {
                                addRowToEdit({ duration_value: 1, duration_unit: 'month', price: '' });
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing product data for edit modal', e);
                    }
                });
            }

            // MODAL DETAIL: Read-Only Populate
            const modalDetail = document.getElementById('modalDetailProduct');
            if (modalDetail) {
                modalDetail.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const rawProduct = btn.getAttribute('data-product');
                    if (!rawProduct) return;

                    try {
                        const product = JSON.parse(rawProduct);
                        const nameEl = document.getElementById('detailProductName');
                        const descEl = document.getElementById('detailProductDesc');
                        const statusEl = document.getElementById('detailProductStatus');
                        const countEl = document.getElementById('detailProductDurationCount');
                        const tableBody = document.getElementById('detailDurationsTableBody');

                        if (nameEl) nameEl.textContent = product.name || '-';
                        if (descEl) descEl.textContent = product.description || 'Tidak ada deskripsi fasilitas tambahan.';
                        if (statusEl) {
                            const isAktif = (product.status === 'active');
                            statusEl.innerHTML = isAktif
                                ? '<span class="badge bg-label-success">Aktif</span>'
                                : '<span class="badge bg-label-secondary">Nonaktif</span>';
                        }

                        const durationCount = (product.durations && product.durations.length) ? product.durations.length : 0;
                        if (countEl) {
                            countEl.textContent = `${durationCount} Pilihan Durasi`;
                        }

                        if (tableBody) {
                            tableBody.innerHTML = '';
                            if (durationCount > 0) {
                                product.durations.forEach(d => {
                                    const durText = d.duration_formatted || `${d.duration_value} ${d.duration_unit}`;
                                    const priceText = d.formatted_price || `Rp ${Number(d.price).toLocaleString('id-ID')}`;
                                    const row = `
                                        <tr style="border-bottom: 1px solid var(--bs-border-color);">
                                            <td class="ps-1 py-3 text-dark fw-medium">
                                                ${durText}
                                            </td>
                                            <td class="pe-1 py-3 text-end fw-bold text-success">${priceText}</td>
                                        </tr>
                                    `;
                                    tableBody.insertAdjacentHTML('beforeend', row);
                                });
                            } else {
                                tableBody.innerHTML = `
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Belum ada pilihan durasi & harga</td>
                                    </tr>
                                `;
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing product data for detail modal', e);
                    }
                });
            }

            // MODAL HAPUS: Populate on show
            const modalHapus = document.getElementById('modalHapusProduct');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const name = btn.getAttribute('data-name') || '';

                    const form = document.getElementById('formHapusProduct');
                    if (form) form.action = action;

                    document.getElementById('hapusProductName').textContent = name;
                });
            }

            // TOGGLE STATUS SWITCH
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            document.querySelectorAll('.toggle-product-switch').forEach(function(switchEl) {
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
                            if (!response.ok) throw new Error('Gagal mengubah status');
                            return response.json();
                        })
                        .then(data => {
                            currentSwitch.disabled = false;
                            if (data.success) {
                                if (badgeEl) {
                                    badgeEl.textContent = data.label;
                                    badgeEl.className = 'badge status-badge ' + (data.status ===
                                        'active' ? 'bg-label-success' : 'bg-label-secondary'
                                    );
                                }
                            } else {
                                currentSwitch.checked = !isChecked;
                            }
                        })
                        .catch(error => {
                            currentSwitch.disabled = false;
                            currentSwitch.checked = !isChecked;
                            alert('Terjadi kesalahan saat mengubah status paket.');
                        });
                });
            });

            // REOPEN MODAL IF VALIDATION FAILED
            @if ($errors->any())
                @if (old('_modal') === 'create_product')
                    const modalTambah = document.getElementById('modalTambahProduct');
                    if (modalTambah && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambah).show();
                    }
                @elseif (old('_modal') === 'edit_product')
                    const prevAction = '{{ old('_action') }}';
                    const formEdit = document.getElementById('formEditProduct');
                    if (formEdit && prevAction) {
                        formEdit.action = prevAction;
                    }
                    const modalEditEl = document.getElementById('modalEditProduct');
                    if (modalEditEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalEditEl).show();
                    }
                @endif
            @endif
        });
    </script>
@endpush