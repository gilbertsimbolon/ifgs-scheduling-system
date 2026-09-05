<div class="modal fade" id="modalHapusPengguna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formHapusPengguna" action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="mb-1">
                        Apakah Anda yakin ingin menghapus pengguna <strong id="hapusPenggunaNama"></strong>?
                    </p>
                    <small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
