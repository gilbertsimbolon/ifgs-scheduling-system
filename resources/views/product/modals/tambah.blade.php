<div class="modal fade" id="modalTambahProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-1 text-primary"></i> Tambah Paket Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_modal" value="create_product">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create_product')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="tambahProductName">
                            Nama Paket / Layanan <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_product') @error('name') is-invalid @enderror @endif"
                            id="tambahProductName" name="name" placeholder="Contoh: Paket Gym 1 Bulan"
                            value="{{ old('_modal') === 'create_product' ? old('name') : '' }}" required />
                        @if (old('_modal') === 'create_product')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahProductDesc">Deskripsi</label>
                        <textarea class="form-control @if (old('_modal') === 'create_product') @error('description') is-invalid @enderror @endif"
                            id="tambahProductDesc" name="description" rows="2"
                            placeholder="Akses seluruh alat fitness, locker, shower...">{{ old('_modal') === 'create_product' ? old('description') : '' }}</textarea>
                        @if (old('_modal') === 'create_product')
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahProductPrice">
                            Harga (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1000" min="0"
                                class="form-control @if (old('_modal') === 'create_product') @error('price') is-invalid @enderror @endif"
                                id="tambahProductPrice" name="price" placeholder="Contoh: 150000"
                                value="{{ old('_modal') === 'create_product' ? old('price') : '' }}" required />
                        </div>
                        @if (old('_modal') === 'create_product')
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tambahDurationValue">
                                Durasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" min="1" max="365"
                                class="form-control @if (old('_modal') === 'create_product') @error('duration_value') is-invalid @enderror @endif"
                                id="tambahDurationValue" name="duration_value" placeholder="1"
                                value="{{ old('_modal') === 'create_product' ? old('duration_value') : '1' }}" required />
                            @if (old('_modal') === 'create_product')
                                @error('duration_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tambahDurationUnit">
                                Satuan Durasi <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @if (old('_modal') === 'create_product') @error('duration_unit') is-invalid @enderror @endif"
                                id="tambahDurationUnit" name="duration_unit" required>
                                <option value="day" {{ old('duration_unit') === 'day' ? 'selected' : '' }}>Hari</option>
                                <option value="week" {{ old('duration_unit') === 'week' ? 'selected' : '' }}>Minggu</option>
                                <option value="month" {{ old('duration_unit', 'month') === 'month' ? 'selected' : '' }}>Bulan</option>
                                <option value="year" {{ old('duration_unit') === 'year' ? 'selected' : '' }}>Tahun</option>
                            </select>
                            @if (old('_modal') === 'create_product')
                                @error('duration_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahProductStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_product') @error('status') is-invalid @enderror @endif"
                            id="tambahProductStatus" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @if (old('_modal') === 'create_product')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Paket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>