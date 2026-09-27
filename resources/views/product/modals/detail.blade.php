<div class="modal fade" id="modalDetailProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-show me-1 text-info"></i> Detail Paket Layanan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Nama Paket</label>
                    <h5 class="fw-bold text-dark mb-0" id="detailProductName">-</h5>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Status</label>
                        <div id="detailProductStatus">
                            <span class="badge bg-label-success">Aktif</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Total Pilihan Durasi</label>
                        <div>
                            <span class="badge bg-label-primary" id="detailProductDurationCount">0 Pilihan Durasi</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small text-uppercase fw-semibold mb-1">Deskripsi Fasilitas</label>
                    <p class="text-body mb-0 bg-light p-3 rounded-2" id="detailProductDesc" style="white-space: pre-wrap;">-</p>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bx bx-list-check text-primary me-1"></i> Pilihan Durasi & Harga
                    </h6>
                    <div class="table-responsive border rounded-2">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Durasi</th>
                                    <th>Harga</th>
                                </tr>
                            </thead>
                            <tbody id="detailDurationsTableBody">
                                <!-- Populated dynamically via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>