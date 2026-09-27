<div class="modal fade" id="modalTambahProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-1 text-primary"></i> Tambah Paket Layanan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST" id="formTambahProduct">
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

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold" for="tambahProductName">
                                Nama Paket / Layanan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'create_product') @error('name') is-invalid @enderror @endif"
                                id="tambahProductName" name="name"
                                placeholder="Contoh: Fitness, Aerobic / Zumba"
                                value="{{ old('_modal') === 'create_product' ? old('name') : '' }}" required />
                            @if (old('_modal') === 'create_product')
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold" for="tambahProductStatus">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                class="form-select @if (old('_modal') === 'create_product') @error('status') is-invalid @enderror @endif"
                                id="tambahProductStatus" name="status" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                    Nonaktif</option>
                            </select>
                            @if (old('_modal') === 'create_product')
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="tambahProductDesc">Deskripsi Fasilitas</label>
                        <textarea class="form-control @if (old('_modal') === 'create_product') @error('description') is-invalid @enderror @endif"
                            id="tambahProductDesc" name="description" rows="2"
                            placeholder="Akses seluruh area fitness, locker, shower...">{{ old('_modal') === 'create_product' ? old('description') : '' }}</textarea>
                        @if (old('_modal') === 'create_product')
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="card border border-light-subtle shadow-none bg-light p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">
                                    <i class="bx bx-time-five text-primary me-1"></i> Pilihan Durasi & Harga
                                </h6>
                                <small class="text-muted">Tambahkan satu atau lebih variasi durasi dan tarif.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnTambahDurationRow">
                                <i class="bx bx-plus me-1"></i> Tambah Durasi
                            </button>
                        </div>

                        <!-- Repeater Table -->
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small fw-semibold">
                                        <th style="width: 25%;">Durasi</th>
                                        <th style="width: 35%;">Satuan Durasi</th>
                                        <th style="width: 32%;">Harga (Rp)</th>
                                        <th style="width: 8%;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tambahDurationsContainer">
                                    <!-- Baris durasi via JavaScript -->
                                </tbody>
                            </table>
                        </div>
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