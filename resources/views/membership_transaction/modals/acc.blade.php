<div class="modal fade" id="modalAccTransaction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-check-circle me-1 text-success"></i> Setujui (ACC) Transaksi Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formAccTransaction">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="mb-2">
                            <i class="bx bx-check-circle text-success display-4"></i>
                        </div>
                        <h6 class="fw-semibold mb-1">Konfirmasi Persetujuan Pembayaran</h6>
                        <p class="text-muted small mb-0">
                            Apakah Anda telah memeriksa bukti transfer dari member <strong id="accTransactionMemberName"></strong>
                            untuk paket <strong id="accTransactionProduct"></strong>?
                        </p>
                    </div>

                    <div class="alert alert-primary d-flex align-items-center" role="alert">
                        <i class="bx bx-info-circle me-2 fs-4"></i>
                        <div class="small">
                            Setelah disetujui (ACC), status transaksi menjadi <strong>Selesai</strong> dan paket membership member akan langsung <strong>Aktif</strong> sesuai durasi paket.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i> Setujui & Aktifkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

