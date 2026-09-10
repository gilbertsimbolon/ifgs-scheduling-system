@extends('layouts.app')

@section('title', 'Paket Layanan Gym')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> Produk Layanan
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
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Digunakan</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
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
                                            <small class="text-muted text-truncate" style="max-width: 280px;">
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
                                    <strong class="text-success">{{ $product->formatted_price }}</strong>
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
                                        <label class="form-check-label cursor-pointer mb-0" for="switchProduct{{ $product->id }}">
                                            @if ($product->status === \App\Models\Product::STATUS_ACTIVE)
                                                <span class="badge bg-label-success status-badge">Aktif</span>
                                            @else
                                                <span class="badge bg-label-secondary status-badge">Non-Aktif</span>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary font-monospace">
                                        {{ $product->memberships_count }} transaksi
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                                            title="Edit Paket" data-bs-toggle="modal" data-bs-target="#modalEditProduct"
                                            data-action="{{ route('products.update', $product) }}"
                                            data-name="{{ $product->name }}"
                                            data-description="{{ $product->description }}"
                                            data-price="{{ (int) $product->price }}"
                                            data-duration-value="{{ $product->duration_value }}"
                                            data-duration-unit="{{ $product->duration_unit }}"
                                            data-status="{{ $product->status }}">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-package fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Belum ada paket layanan</h6>
                                        <span class="text-muted">Klik tombol "Tambah Paket" untuk membuat paket membership gym baru.</span>
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
                        Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }} paket
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
    @include('product.modals.tambah')
    @include('product.modals.edit')
    @include('product.modals.hapus')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Modal Populate
            const modalEdit = document.getElementById('modalEditProduct');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function(event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;

                    const action = btn.getAttribute('data-action') || '';
                    const name = btn.getAttribute('data-name') || '';
                    const desc = btn.getAttribute('data-description') || '';
                    const price = btn.getAttribute('data-price') || '';
                    const durationVal = btn.getAttribute('data-duration-value') || '1';
                    const durationUnit = btn.getAttribute('data-duration-unit') || 'month';
                    const status = btn.getAttribute('data-status') || 'active';

                    const form = document.getElementById('formEditProduct');
                    if (form) form.action = action;

                    const actionInput = document.getElementById('editProductActionInput');
                    if (actionInput) actionInput.value = action;

                    document.getElementById('editProductName').value = name;
                    document.getElementById('editProductDesc').value = desc;
                    document.getElementById('editProductPrice').value = price;
                    document.getElementById('editProductDurationValue').value = durationVal;
                    document.getElementById('editProductDurationUnit').value = durationUnit;
                    document.getElementById('editProductStatus').value = status;
                });
            }

            // Hapus Modal Populate
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

            // Toggle Status Switch
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
                                badgeEl.className = 'badge status-badge ' + (data.status === 'active' ? 'bg-label-success' : 'bg-label-secondary');
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

            // Reopen modal if validation failed
            @if ($errors->any())
                @if (old('_modal') === 'create_product')
                    const modalTambahEl = document.getElementById('modalTambahProduct');
                    if (modalTambahEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambahEl).show();
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
