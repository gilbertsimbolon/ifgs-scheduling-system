<div class="modal fade" id="modalHapusMembership" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-trash me-1 text-danger"></i> Hapus Membership
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="formHapusMembership">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="mb-3">
                            <i class="bx bx-trash text-danger display-4"></i>
                        </div>
                        <h6 class="fw-semibold">Apakah Anda yakin ingin menghapus data membership ini?</h6>
                        <p class="text-muted mb-0">
                            Data membership member <strong id="hapusMembershipMemberName"></strong>
                            akan dihapus permanen dari sistem. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Ya, Hapus Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>