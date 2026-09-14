<div class="modal fade" id="modalEditPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit-alt me-1 text-warning"></i> Edit Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="formEditPaymentMethod">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit_payment_method">
                <input type="hidden" name="_action" id="editPaymentMethodActionInput" value="">

                <div class="modal-body">
                    @if ($errors->any() && old('_modal') === 'edit_payment_method')
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
                        <label class="form-label" for="editPaymentMethodName">
                            Nama Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit_payment_method') @error('name') is-invalid @enderror @endif"
                            id="editPaymentMethodName" name="name"
                            value="{{ old('_modal') === 'edit_payment_method' ? old('name') : '' }}" required />
                        <div class="form-text text-muted small">
                            <i class="bx bx-check-circle text-success me-1"></i> Kode unik sistem akan otomatis disinkronkan dari nama metode.
                        </div>
                        @if (old('_modal') === 'edit_payment_method')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Tipe Pembayaran -->
                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodType">
                            Tipe Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_payment_method') @error('type') is-invalid @enderror @endif"
                            id="editPaymentMethodType" name="type" required>
                            <option value="cash">Tunai (Bayar di Kasir)</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet (GoPay, OVO, Dana, ShopeePay)</option>
                            <option value="qris">QRIS (Scan Barcode)</option>
                        </select>
                        @if (old('_modal') === 'edit_payment_method')
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <!-- Field Khusus: Transfer Bank & E-Wallet (Nomor Rekening / HP) -->
                    <div id="editContainerAccount" class="d-none">
                        <div class="mb-3">
                            <label class="form-label" for="editPaymentMethodAccountNo" id="editAccountNoLabel">
                                Nomor Rekening / No. HP <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control font-monospace @if (old('_modal') === 'edit_payment_method') @error('account_number') is-invalid @enderror @endif"
                                id="editPaymentMethodAccountNo" name="account_number"
                                placeholder="Contoh: 1234567890 atau 08123456789"
                                value="{{ old('_modal') === 'edit_payment_method' ? old('account_number') : '' }}" />
                            @if (old('_modal') === 'edit_payment_method')
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="editPaymentMethodAccountName">
                                Atas Nama (A/N) / Pemilik Akun
                            </label>
                            <input type="text"
                                class="form-control @if (old('_modal') === 'edit_payment_method') @error('account_name') is-invalid @enderror @endif"
                                id="editPaymentMethodAccountName" name="account_name"
                                placeholder="Contoh: Indo Fitness Gym Sport"
                                value="{{ old('_modal') === 'edit_payment_method' ? old('account_name') : '' }}" />
                            @if (old('_modal') === 'edit_payment_method')
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Field Khusus: QRIS (Gambar Barcode QRIS) -->
                    <div id="editContainerQris" class="d-none">
                        <div class="mb-3" id="editCurrentQrWrapper">
                            <label class="form-label d-block">Gambar Barcode QRIS Saat Ini</label>
                            <div class="p-2 border rounded bg-light d-inline-block">
                                <img id="editCurrentQrImage" src="" alt="QRIS Code" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="editPaymentMethodQrImage">
                                Ganti Gambar QRIS (Opsional)
                            </label>
                            <input type="file"
                                class="form-control @if (old('_modal') === 'edit_payment_method') @error('qr_image') is-invalid @enderror @endif"
                                id="editPaymentMethodQrImage" name="qr_image" accept="image/*" />
                            <div class="form-text text-muted small">
                                Kosongkan jika tidak ingin mengubah gambar QRIS saat ini.
                            </div>
                            @if (old('_modal') === 'edit_payment_method')
                                @error('qr_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <!-- Field Khusus: Cash Information Box -->
                    <div id="editContainerCash" class="mb-3">
                        <div class="alert alert-light border small mb-0 d-flex align-items-center">
                            <i class="bx bx-info-circle text-primary fs-4 me-2"></i>
                            <div>
                                Metode tunai tidak memerlukan nomor rekening atau kode QRIS. Pelanggan membayar langsung di meja kasir.
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_payment_method') @error('status') is-invalid @enderror @endif"
                            id="editPaymentMethodStatus" name="status" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Non-Aktif</option>
                        </select>
                        @if (old('_modal') === 'edit_payment_method')
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
