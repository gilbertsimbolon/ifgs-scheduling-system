<div class="modal fade" id="modalTambahPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-1 text-primary"></i> Tambah Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payment-methods.store') }}" method="POST">
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

                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodName">
                            Nama Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control @if (old('_modal') === 'create_payment_method') @error('name') is-invalid @enderror @endif"
                            id="tambahPaymentMethodName" name="name" placeholder="Contoh: Transfer Bank Mandiri"
                            value="{{ old('_modal') === 'create_payment_method' ? old('name') : '' }}" required />
                        @if (old('_modal') === 'create_payment_method')
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodCode">
                            Kode Unik <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control font-monospace @if (old('_modal') === 'create_payment_method') @error('code') is-invalid @enderror @endif"
                            id="tambahPaymentMethodCode" name="code" placeholder="Contoh: bank_mandiri"
                            value="{{ old('_modal') === 'create_payment_method' ? old('code') : '' }}" required />
                        <div class="form-text text-muted small">
                            Hanya huruf, angka, tanda hubung (-), dan garis bawah (_).
                        </div>
                        @if (old('_modal') === 'create_payment_method')
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tambahPaymentMethodStatus">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @if (old('_modal') === 'create_payment_method') @error('status') is-invalid @enderror @endif"
                            id="tambahPaymentMethodStatus" name="status" required>
                            <option value="active" {{ old('_modal') === 'create_payment_method' && old('status') === 'inactive' ? '' : 'selected' }}>
                                Aktif (Dapat digunakan transaksi)
                            </option>
                            <option value="inactive" {{ old('_modal') === 'create_payment_method' && old('status') === 'inactive' ? 'selected' : '' }}>
                                Non-Aktif (Disembunyikan dari transaksi baru)
                            </option>
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
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

