<div class="modal fade" id="modalTambahPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-1 text-primary"></i> Tambah Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payment-methods.store') }}" method="POST" enctype="multipart/form-data" id="formTambahPaymentMethod">
                @csrf
                <input type="hidden" name="_modal" value="create_payment_method">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'create_payment_method')
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Nama Metode -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodName">
                            Nama Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_payment_method') @error('name') is-invalid @enderror @endif"
                            id="tambahPaymentMethodName" name="name" placeholder="Contoh: BCA, DANA, QRIS Bank Mandiri"
                            value="{{ old('_modal') === 'create_payment_method' ? old('name') : '' }}" required />
                        <div class="form-text text-muted small">
                            <i class="bx bx-check-circle text-success me-1"></i> Kode unik sistem akan dibuat otomatis dari nama metode.
                        </div>
                        @if (old('_modal') === 'create_payment_method')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Tipe / Kategori Pembayaran -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodType">
                            Tipe Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_payment_method') @error('type') is-invalid @enderror @endif"
                            id="tambahPaymentMethodType" name="type" required>
                            <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>Tunai (Bayar di Kasir)</option>
                            <option value="bank_transfer" {{ old('type') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="ewallet" {{ old('type') === 'ewallet' ? 'selected' : '' }}>E-Wallet (GoPay, OVO, Dana, ShopeePay)</option>
                            <option value="qris" {{ old('type') === 'qris' ? 'selected' : '' }}>QRIS (Scan Barcode)</option>
                        </select>
                        @if (old('_modal') === 'create_payment_method')
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Field Khusus: Transfer Bank & E-Wallet (Nomor Rekening / HP) -->
                    <div id="tambahContainerAccount" class="d-none">
                        <div class="mb-3">
                            <label class="form-label" for="tambahPaymentMethodAccountNo" id="tambahAccountNoLabel">
                                Nomor Rekening / No. HP <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control font-monospace @if (old('_modal') === 'create_payment_method') @error('account_number') is-invalid @enderror @endif"
                                id="tambahPaymentMethodAccountNo" name="account_number"
                                placeholder="Contoh: 1234567890 atau 08123456789"
                                value="{{ old('_modal') === 'create_payment_method' ? old('account_number') : '' }}" />
                            @if (old('_modal') === 'create_payment_method')
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tambahPaymentMethodAccountName">
                                Atas Nama (A/N) / Pemilik Akun
                            </label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'create_payment_method') @error('account_name') is-invalid @enderror @endif"
                                id="tambahPaymentMethodAccountName" name="account_name"
                                placeholder="Contoh: Indo Fitness Gym Sport"
                                value="{{ old('_modal') === 'create_payment_method' ? old('account_name') : '' }}" />
                            @if (old('_modal') === 'create_payment_method')
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Field Khusus: QRIS (Upload Gambar Barcode QRIS) -->
                    <div id="tambahContainerQris" class="d-none">
                        <div class="mb-3">
                            <label class="form-label" for="tambahPaymentMethodQrImage">
                                Upload Gambar / Barcode QRIS
                            </label>
                            <input type="file"
                                class="form-control @if (old('_modal') === 'create_payment_method') @error('qr_image') is-invalid @enderror @endif"
                                id="tambahPaymentMethodQrImage" name="qr_image" accept="image/*" />
                            <div class="form-text text-muted small">
                                Format gambar: JPG, PNG, WEBP, atau SVG. Maksimal 2MB.
                            </div>
                            @if (old('_modal') === 'create_payment_method')
                                @error('qr_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Field Khusus: Cash Information Box -->
                    <div id="tambahContainerCash" class="mb-3">
                        <div class="alert alert-light border small mb-0 d-flex align-items-center">
                            <i class="bx bx-info-circle text-primary fs-4 me-2"></i>
                            <div>
                                Metode tunai tidak memerlukan nomor rekening atau kode QRIS. Pelanggan membayar langsung di meja kasir.
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_payment_method') @error('status') is-invalid @enderror @endif"
                            id="tambahPaymentMethodStatus" name="status" required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                        @if (old('_modal') === 'create_payment_method')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Metode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
