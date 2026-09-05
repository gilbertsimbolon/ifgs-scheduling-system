<div class="modal fade" id="modalDetailPengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span id="detailAvatarInitial" class="avatar-initial rounded-circle bg-label-primary fs-4">
                            --
                        </span>
                    </div>
                    <h5 id="detailNama" class="mb-0"></h5>
                    <p class="text-muted mb-0"><code id="detailSlug"></code></p>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" style="width: 35%;">
                                    <i class="bx bx-envelope me-1 text-primary"></i> Email
                                </th>
                                <td>: <span id="detailEmail"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-shield-quarter me-1 text-primary"></i> Peran
                                </th>
                                <td>: <span id="detailRole" class="badge bg-label-primary"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-check-circle me-1 text-primary"></i> Status
                                </th>
                                <td>: <span id="detailStatus" class="badge bg-label-success"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-calendar me-1 text-primary"></i> Dibuat pada
                                </th>
                                <td>: <span id="detailCreatedAt" class="text-muted"></span></td>
                            </tr>
                            <tr>
                                <th class="ps-0">
                                    <i class="bx bx-time me-1 text-primary"></i> Diperbarui pada
                                </th>
                                <td>: <span id="detailUpdatedAt" class="text-muted"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
