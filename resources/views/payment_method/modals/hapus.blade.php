<div class="modal fade" id="modalHapusPaymentMethod" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-trash me-1 text-danger"></i> Hapus Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formHapusPaymentMethod">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center">
                    <div class="text-danger mb-3">
                        <i class="bx bx-error-circle" style="font-size: 3.5rem;"></i>
                    </div>
                    <p class="mb-1">Apakah Anda yakin ingin menghapus metode pembayaran:</p>
                    <h6 class="fw-bold text-danger mb-2" id="hapusPaymentMethodName"></h6>

                    <div id="hapusPaymentMethodWarning" class="alert alert-warning py-2 px-3 text-start small mb-0 d-none">
                        <i class="bx bx-info-circle me-1"></i>
                        Metode pembayaran ini sudah digunakan dalam transaksi dan tidak dapat dihapus. Anda hanya dapat mengubah statusnya menjadi <strong>Non-Aktif</strong>.
                    </div>
                    <small id="hapusPaymentMethodSubtext" class="text-muted d-block mt-2">
                        Tindakan ini tidak dapat dibatalkan jika belum memiliki riwayat transaksi.
                    </small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmHapusPaymentMethod">
                        <i class="bx bx-trash me-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

