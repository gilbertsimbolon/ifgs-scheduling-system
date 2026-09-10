<div class="modal fade" id="modalEditPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-edit-alt me-1 text-warning"></i> Edit Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formEditPaymentMethod">
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

                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodName">
                            Nama Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'edit_payment_method') @error('name') is-invalid @enderror @endif"
                            id="editPaymentMethodName" name="name"
                            value="{{ old('_modal') === 'edit_payment_method' ? old('name') : '' }}" required />
                        @if (old('_modal') === 'edit_payment_method')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodCode">
                            Kode Unik <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control font-monospace @if (old('_modal') === 'edit_payment_method') @error('code') is-invalid @enderror @endif"
                            id="editPaymentMethodCode" name="code"
                            value="{{ old('_modal') === 'edit_payment_method' ? old('code') : '' }}" required />
                        <div class="form-text text-muted small">
                            Hanya huruf, angka, tanda hubung (-), dan garis bawah (_).
                        </div>
                        @if (old('_modal') === 'edit_payment_method')
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="editPaymentMethodStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'edit_payment_method') @error('status') is-invalid @enderror @endif"
                            id="editPaymentMethodStatus" name="status" required>
                            <option value="active">Aktif (Dapat digunakan transaksi)</option>
                            <option value="inactive">Non-Aktif (Disembunyikan dari transaksi baru)</option>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

