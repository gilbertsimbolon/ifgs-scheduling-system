<div class="modal fade" id="modalEditProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit-alt me-1 text-warning"></i> Edit Paket Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditProduct">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit_product">
                <input type="hidden" name="_action" id="editProductActionInput" value="">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'edit_product')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="editProductName">
                            Nama Paket / Layanan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit_product') @error('name') is-invalid @enderror @endif"
                            id="editProductName" name="name" required />
                        @if (old('_modal') === 'edit_product')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editProductDesc">Deskripsi</label>
                        <textarea class="form-control @if (old('_modal') === 'edit_product') @error('description') is-invalid @enderror @endif"
                            id="editProductDesc" name="description" rows="2"></textarea>
                        @if (old('_modal') === 'edit_product')
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editProductPrice">
                            Harga (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1000" min="0"
                                class="form-control @if (old('_modal') === 'edit_product') @error('price') is-invalid @enderror @endif"
                                id="editProductPrice" name="price" required />
                        </div>
                        @if (old('_modal') === 'edit_product')
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editProductDurationValue">
                                Durasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="1" max="365"
                                class="form-control @if (old('_modal') === 'edit_product') @error('duration_value') is-invalid @enderror @endif"
                                id="editProductDurationValue" name="duration_value" required />
                            @if (old('_modal') === 'edit_product')
                                @error('duration_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="editProductDurationUnit">
                                Satuan Durasi <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @if (old('_modal') === 'edit_product') @error('duration_unit') is-invalid @enderror @endif"
                                id="editProductDurationUnit" name="duration_unit" required>
                                <option value="day">Hari</option>
                                <option value="week">Minggu</option>
                                <option value="month">Bulan</option>
                                <option value="year">Tahun</option>
                            </select>
                            @if (old('_modal') === 'edit_product')
                                @error('duration_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editProductStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_product') @error('status') is-invalid @enderror @endif"
                            id="editProductStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Non-Aktif</option>
                        </select>
                        @if (old('_modal') === 'edit_product')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>