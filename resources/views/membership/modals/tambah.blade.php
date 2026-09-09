<div class="modal fade" id="modalTambahMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-credit-card me-1 text-primary"></i> Tambah Transaksi Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('memberships.store') }}" method="POST" id="formTambahMembership">
                @csrf
                <input type="hidden" name="_modal" value="create_membership">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create_membership')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Pilih Member -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahMemberId">
                            Pilih Member <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_membership') @error('member_id') is-invalid @enderror @endif"
                            id="tambahMemberId" name="member_id" required>
                            <option value="">-- Pilih Member Terdaftar --</option>
                            @foreach ($activeMembers as $memberItem)
                                <option value="{{ $memberItem->id }}"
                                    {{ old('_modal') === 'create_membership' && old('member_id') == $memberItem->id ? 'selected' : '' }}>
                                    {{ $memberItem->user?->name ?? 'Tanpa Nama' }} ({{ $memberItem->member_code }})
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'create_membership')
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif

                        @if ($activeMembers->isEmpty())
                            <div class="form-text text-warning mt-1">
                                <i class="bx bx-info-circle me-1"></i> Belum ada data member.
                                <a href="{{ route('member.index') }}">Daftarkan member terlebih dahulu</a>.
                            </div>
                        @endif
                    </div>

                    <!-- Pilih Paket Layanan -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahProductId">
                            Paket Layanan / Produk <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_membership') @error('product_id') is-invalid @enderror @endif"
                            id="tambahProductId" name="product_id" required>
                            <option value="">-- Pilih Paket Layanan --</option>
                            @foreach ($activeProducts as $productItem)
                                <option value="{{ $productItem->id }}"
                                    data-price="{{ $productItem->price }}"
                                    data-duration-value="{{ $productItem->duration_value }}"
                                    data-duration-unit="{{ $productItem->duration_unit }}"
                                    data-duration-label="{{ $productItem->duration_formatted }}"
                                    {{ old('_modal') === 'create_membership' && old('product_id') == $productItem->id ? 'selected' : '' }}>
                                    {{ $productItem->name }} — {{ $productItem->formatted_price }} ({{ $productItem->duration_formatted }})
                                </option>
                            @endforeach
                        </select>
                        @if (old('_modal') === 'create_membership')
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif

                        @if ($activeProducts->isEmpty())
                            <div class="form-text text-warning mt-1">
                                <i class="bx bx-info-circle me-1"></i> Belum ada paket layanan aktif.
                                <a href="{{ route('products.index') }}">Buat paket layanan</a> terlebih dahulu.
                            </div>
                        @endif
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tambahStartDate">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                class="form-control @if (old('_modal') === 'create_membership') @error('start_date') is-invalid @enderror @endif"
                                id="tambahStartDate" name="start_date"
                                value="{{ old('_modal') === 'create_membership' ? old('start_date') : date('Y-m-d') }}"
                                required />
                            @if (old('_modal') === 'create_membership')
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <!-- Tanggal Berakhir (Otomatis / Opsional) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tambahEndDate">
                                Tanggal Berakhir
                            </label>
                            <input type="date"
                                class="form-control @if (old('_modal') === 'create_membership') @error('end_date') is-invalid @enderror @endif"
                                id="tambahEndDate" name="end_date"
                                value="{{ old('_modal') === 'create_membership' ? old('end_date') : '' }}"
                                placeholder="Dihitung otomatis" />
                            <div class="form-text text-muted small">
                                Dihitung otomatis dari durasi paket.
                            </div>
                            @if (old('_modal') === 'create_membership')
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Biaya Paket (Snapshot) -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahPrice">
                            Biaya Transaksi (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1000" min="0"
                                class="form-control @if (old('_modal') === 'create_membership') @error('price') is-invalid @enderror @endif"
                                id="tambahPrice" name="price" placeholder="Contoh: 150000"
                                value="{{ old('_modal') === 'create_membership' ? old('price') : '' }}" />
                        </div>
                        <div class="form-text text-muted small">
                            Tersimpan permanen sebagai riwayat transaksi (snapshot).
                        </div>
                        @if (old('_modal') === 'create_membership')
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanMembership">
                        <i class="bx bx-save me-1"></i> Simpan Membership
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>