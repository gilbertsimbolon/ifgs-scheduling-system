<div class="modal fade" id="modalTolakTransaction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-x-circle me-1 text-danger"></i> Tolak Transaksi Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formTolakTransaction">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="mb-2">
                            <i class="bx bx-error-circle text-danger display-4"></i>
                        </div>
                        <h6 class="fw-semibold mb-1">Tolak Pesanan & Bukti Pembayaran?</h6>
                        <p class="text-muted small mb-0">
                            Pesanan dari member <strong id="tolakTransactionMemberName"></strong> untuk paket
                            <strong id="tolakTransactionProduct"></strong> akan ditolak.
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="tolakTransactionReason" class="form-label required fw-semibold">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="tolakTransactionReason" name="reason" rows="3" required
                            placeholder="Contoh: Nominal transfer kurang, bukti transfer tidak jelas/buram, dana belum masuk ke mutasi rekening kasir."></textarea>
                        <div class="form-text">Alasan penolakan akan dicatat dan dapat dilihat oleh member/pengelola.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-x me-1"></i> Tolak Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

