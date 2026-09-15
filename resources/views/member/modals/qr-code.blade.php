<!-- Modal Preview & Cetak QR Code Member (Admin/Kasir) -->
<div class="modal fade" id="modalQrCodeAdmin" tabindex="-1" aria-labelledby="modalQrCodeAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center shadow border-0">
            <div class="modal-header border-bottom pb-3">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold text-heading" id="modalQrCodeAdminLabel">
                        <i class="bx bx-qr-scan me-1 text-primary"></i> QR Code Absensi Member
                    </h5>
                    <small class="text-muted">Identitas Digital & Absensi Indo Fitness Gym Sport</small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-0" id="qrModalMemberName">-</h5>
                    <p class="text-muted small mb-0">Kode Member: <code class="text-primary fw-bold fs-6" id="qrModalMemberCode">-</code></p>
                </div>

                <!-- Kontainer Gambar / SVG QR Code -->
                <div class="d-inline-block p-3 bg-white rounded-3 border border-2 border-primary shadow-sm my-2" style="max-width: 250px;">
                    <img id="qrModalImage" src="" alt="QR Code" class="img-fluid" style="width: 200px; height: 200px; object-fit: contain;">
                </div>

                <div class="mt-2">
                    <span class="badge bg-label-secondary font-monospace" id="qrModalQrCode">-</span>
                </div>

                <div class="alert alert-light border py-2 px-3 mt-3 mb-0 text-start small">
                    <i class="bx bx-info-circle me-1 text-primary"></i>
                    Kode QR ini digunakan untuk proses absensi mandiri atau check-in resepsionis saat member berkunjung ke gym.
                </div>
            </div>
            <div class="modal-footer justify-content-center border-top pt-3 pb-3 bg-light">
                <a href="#" id="qrModalDownloadBtn" class="btn btn-primary">
                    <i class="bx bx-download me-1"></i> Unduh File QR (SVG)
                </a>
                <a href="#" id="qrModalCardBtn" target="_blank" class="btn btn-outline-secondary">
                    <i class="bx bx-printer me-1"></i> Cetak Kartu Member
                </a>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

