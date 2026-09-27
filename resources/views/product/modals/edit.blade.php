<div class="modal fade" id="modalEditProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark mb-0">
                    <i class="bx bx-edit-alt text-primary me-1"></i> Edit Paket Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditProduct">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit_product">
                <input type="hidden" name="_action" id="editProductActionInput" value="">

                <div class="modal-body py-4">
                    @if ($errors->any() && old('_modal') === 'edit_product')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold text-dark" for="editProductName">
                                Nama Paket / Layanan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'edit_product') @error('name') is-invalid @enderror @endif"
                                id="editProductName" name="name"
                                placeholder="Contoh: Fitness, Aerobic / Zumba"
                                value="{{ old('_modal') === 'edit_product' ? old('name') : '' }}" required />
                            @if (old('_modal') === 'edit_product')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-dark" for="editProductStatus">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                class="form-select @if (old('_modal') === 'edit_product') @error('status') is-invalid @enderror @endif"
                                id="editProductStatus" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                            @if (old('_modal') === 'edit_product')
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark" for="editProductDesc">Deskripsi Fasilitas</label>
                        <textarea class="form-control @if (old('_modal') === 'edit_product') @error('description') is-invalid @enderror @endif"
                            id="editProductDesc" name="description" rows="2"
                            placeholder="Akses seluruh area fitness, locker, shower...">{{ old('_modal') === 'edit_product' ? old('description') : '' }}</textarea>
                        @if (old('_modal') === 'edit_product')
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Section Pilihan Durasi & Harga (Clean White Container) -->
                    <div class="border rounded-3 p-3 bg-white mb-2">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bx bx-time-five text-primary me-1"></i> Pilihan Durasi &amp; Harga
                                </h6>
                                <p class="text-muted small mb-0">Kelola variasi durasi dan tarif untuk paket ini.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnEditAddDurationRow">
                                <i class="bx bx-plus me-1"></i> Tambah Durasi
                            </button>
                        </div>

                        <!-- Repeater Table -->
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small fw-semibold" style="border-bottom: 2px solid var(--bs-border-color);">
                                        <th class="ps-1 pb-2" style="width: 25%;">DURASI</th>
                                        <th class="pb-2" style="width: 35%;">SATUAN DURASI</th>
                                        <th class="pb-2" style="width: 32%;">HARGA (RP)</th>
                                        <th class="text-center pe-1 pb-2" style="width: 8%;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="editDurationsContainer">
                                    <!-- Baris durasi dimuat otomatis via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>