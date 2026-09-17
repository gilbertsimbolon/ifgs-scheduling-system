<div class="modal fade" id="modalOrderMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-cart-add me-1 text-primary"></i> Pendaftaran & Pembayaran Paket Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('memberships.order') }}" method="POST" enctype="multipart/form-data"
                id="formOrderMembership">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- 1. Pilihan Paket Layanan -->
                        <div class="col-md-6">
                            <label for="orderProductId" class="form-label required fw-semibold">
                                Pilih Paket Layanan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="orderProductId"
                                name="product_id" required>
                                <option value="">-- Pilih Paket Gym --</option>
                                @foreach ($activeProducts as $prod)
                                    <option value="{{ $prod->id }}" data-price="{{ (float) $prod->price }}"
                                        data-formatted-price="{{ $prod->formatted_price }}"
                                        data-duration="{{ $prod->duration_formatted }}"
                                        {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->name }} &bull; {{ $prod->duration_formatted }}
                                        ({{ $prod->formatted_price }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 2. Tanggal Mulai -->
                        <div class="col-md-6">
                            <label for="orderStartDate" class="form-label required fw-semibold">
                                Tanggal Mulai Latihan <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                id="orderStartDate" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                                min="{{ date('Y-m-d') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 3. Metode Pembayaran -->
                        <div class="col-md-12">
                            <label for="orderPaymentMethodId" class="form-label required fw-semibold">
                                Metode Pembayaran Transfer / QRIS <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('payment_method_id') is-invalid @enderror"
                                id="orderPaymentMethodId" name="payment_method_id" required>
                                <option value="">-- Pilih Rekening Pembayaran --</option>
                                @foreach ($activePaymentMethods as $pm)
                                    <option value="{{ $pm->id }}" data-type="{{ $pm->type }}"
                                        data-account-name="{{ $pm->account_name }}"
                                        data-account-number="{{ $pm->account_number }}"
                                        data-qr-image="{{ $pm->qr_image ? asset('storage/' . $pm->qr_image) : '' }}"
                                        {{ old('payment_method_id') == $pm->id ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $pm->name)) }}
                                        @if ($pm->account_number)
                                            ({{ $pm->account_number }} a/n {{ $pm->account_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Instruksi Pembayaran Dinamis -->
                        <div class="col-md-12">
                            <div id="orderPaymentInstruction" class="card bg-lighter border border-primary p-3 d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-primary"><i class="bx bx-info-circle me-1"></i> Petunjuk
                                        Transfer</span>
                                    <span class="badge bg-label-success fs-6" id="orderSummaryPrice">Rp 0</span>
                                </div>
                                <div id="orderBankDetails" class="small text-muted mb-2">
                                    Silakan transfer ke nomor rekening: <strong id="orderAccountNumber"
                                        class="text-dark font-monospace fs-6"></strong>
                                    a/n <strong id="orderAccountName" class="text-dark"></strong>
                                </div>
                                <div id="orderQrDetails" class="text-center my-2 d-none">
                                    <img id="orderQrImg" src="" alt="QRIS IFGS"
                                        class="img-fluid rounded border shadow-sm p-1 bg-white"
                                        style="max-height: 180px;">
                                    <p class="small text-muted mt-1 mb-0">Scan kode QRIS di atas melalui mobile banking
                                        / e-wallet Anda.</p>
                                </div>
                                <small class="text-muted fst-italic">Pastikan nominal transfer tepat sesuai harga paket
                                    di atas.</small>
                            </div>
                        </div>

                        <!-- 4. Upload Bukti Transfer -->
                        <div class="col-md-12">
                            <label for="orderPaymentProof" class="form-label required fw-semibold">
                                Unggah Screenshot / Foto Bukti Transfer <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control @error('payment_proof') is-invalid @enderror"
                                id="orderPaymentProof" name="payment_proof"
                                accept="image/png,image/jpeg,image/jpg,image/webp" required>
                            <div class="form-text">Format: JPG, JPEG, PNG, atau WEBP. Maksimal ukuran file: 5MB.</div>
                            @error('payment_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Image Preview Thumbnail -->
                            <div id="orderProofPreviewContainer"
                                class="mt-2 d-none text-center p-2 border rounded bg-light">
                                <span class="small text-muted d-block mb-1">Preview Bukti Transfer:</span>
                                <img id="orderProofPreviewImg" src="" alt="Preview Bukti Transfer"
                                    class="img-thumbnail" style="max-height: 160px;">
                            </div>
                        </div>

                        <!-- 5. Catatan Tambahan (Opsional) -->
                        <div class="col-md-12">
                            <label for="orderNotes" class="form-label fw-semibold">Catatan Tambahan (Opsional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="orderNotes" name="notes" rows="2"
                                placeholder="Tuliskan catatan atau referensi nama rekening pengirim jika berbeda...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm" id="btnSubmitOrder">
                        <i class="bx bx-send me-1"></i> Kirim Pesanan & Bukti Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
