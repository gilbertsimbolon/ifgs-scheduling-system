@extends('layouts.app')

@section('title', 'FAQ Landing Page')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-1 mb-0">
                    <span class="text-muted fw-light">Manajemen /</span> FAQ Landing Page
                </h5>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahFaq">
                    <i class="bx bx-plus me-1"></i> Tambah FAQ
                </button>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Global Validation Alert -->
        @if (isset($errors) && $errors->any() && !old('_modal'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <h6 class="alert-heading fw-bold mb-1">Terjadi kesalahan input:</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter & Table Card -->
        <div class="card">
            <!-- Search and Filter Form -->
            <div class="card-body border-bottom">
                <form action="{{ route('faqs.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="search">Cari Pertanyaan / Jawaban</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Ketik kata kunci pertanyaan..." value="{{ request('search') }}" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="status">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non-Aktif
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ route('faqs.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-reset"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabel FAQ -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Pertanyaan</th>
                            <th>Jawaban</th>
                            <th style="width: 100px;">Urutan</th>
                            <th style="width: 130px;">Status</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($faqs as $faq)
                            <tr>
                                <td>{{ $faqs->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div class="avatar avatar-sm me-2 flex-shrink-0">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-help-circle"></i>
                                            </span>
                                        </div>
                                        <div style="white-space: normal; max-width: 320px;">
                                            <span class="fw-semibold text-heading">{{ $faq->question }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="white-space: normal; max-width: 380px;" class="text-muted small">
                                        {{ \Illuminate\Support\Str::limit($faq->answer, 110) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info font-monospace fs-7">#{{ $faq->order }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input cursor-pointer toggle-status-switch"
                                                type="checkbox" role="switch" id="switchFaq{{ $faq->id }}"
                                                data-action="{{ route('faqs.toggle-status', $faq) }}"
                                                title="Klik untuk ubah status" {{ $faq->is_active ? 'checked' : '' }}>
                                        </div>
                                        <label class="form-check-label cursor-pointer mb-0"
                                            for="switchFaq{{ $faq->id }}">
                                            @if ($faq->is_active)
                                                <span class="badge bg-label-success status-badge">Aktif</span>
                                            @else
                                                <span class="badge bg-label-secondary status-badge">Non-Aktif</span>
                                            @endif
                                        </label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning btn-edit-faq"
                                            title="Edit FAQ" data-bs-toggle="modal" data-bs-target="#modalEditFaq"
                                            data-action="{{ route('faqs.update', $faq) }}"
                                            data-question="{{ $faq->question }}" data-answer="{{ $faq->answer }}"
                                            data-order="{{ $faq->order }}"
                                            data-active="{{ $faq->is_active ? '1' : '0' }}">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-outline-danger btn-delete-faq"
                                            title="Hapus FAQ" data-bs-toggle="modal" data-bs-target="#modalHapusFaq"
                                            data-action="{{ route('faqs.destroy', $faq) }}"
                                            data-question="{{ $faq->question }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-help-circle fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">
                                            @if (request()->hasAny(['search', 'status']))
                                                Tidak ada FAQ yang sesuai pencarian
                                            @else
                                                Belum ada pertanyaan FAQ
                                            @endif
                                        </h6>
                                        <span class="text-muted">
                                            @if (request()->hasAny(['search', 'status']))
                                                Coba ubah kata kunci pencarian atau reset filter.
                                            @else
                                                Klik tombol "+ Tambah FAQ" untuk menambahkan data baru ke landing page.
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">
                    @if ($faqs->total() > 0)
                        Menampilkan {{ $faqs->firstItem() }}–{{ $faqs->lastItem() }} dari
                        {{ $faqs->total() }} data FAQ
                    @else
                        Tidak ada data
                    @endif
                </small>
                <div>
                    {{ $faqs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah FAQ -->
    <div class="modal fade" id="modalTambahFaq" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('faqs.store') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="_modal" value="create_faq">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bx bx-plus-circle me-1 text-primary"></i> Tambah Pertanyaan FAQ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="add_question">Pertanyaan <span
                                class="text-danger">*</span></label>
                        <input type="text" id="add_question" name="question"
                            class="form-control @error('question') is-invalid @enderror"
                            value="{{ old('_modal') === 'create_faq' ? old('question') : '' }}"
                            placeholder="Contoh: Kapan jam buka operasional gym?" required>
                        @error('question')
                            @if (old('_modal') === 'create_faq')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="add_answer">Jawaban <span class="text-danger">*</span></label>
                        <textarea id="add_answer" name="answer" rows="4" class="form-control @error('answer') is-invalid @enderror"
                            placeholder="Tuliskan jawaban lengkap yang akan tampil di accordion FAQ..." required>{{ old('_modal') === 'create_faq' ? old('answer') : '' }}</textarea>
                        @error('answer')
                            @if (old('_modal') === 'create_faq')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_order">Nomor Urutan Tampil</label>
                            <input type="number" id="add_order" name="order" min="0"
                                class="form-control @error('order') is-invalid @enderror"
                                value="{{ old('_modal') === 'create_faq' ? old('order') : '' }}"
                                placeholder="Otomatis jika kosong">
                            @error('order')
                                @if (old('_modal') === 'create_faq')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @endif
                            @enderror
                            <small class="text-muted">Urutan tampil di accordion landing page.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="add_is_active" name="is_active"
                                    value="1"
                                    {{ old('_modal') === 'create_faq' ? (old('is_active') ? 'checked' : '') : 'checked' }}>
                                <label class="form-check-label fw-semibold" for="add_is_active">Aktif di Landing
                                    Page</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit FAQ -->
    <div class="modal fade" id="modalEditFaq" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditFaq" action="" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit_faq">
                <input type="hidden" name="_action" id="edit_action_fallback" value="{{ old('_action') }}">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bx bx-edit me-1 text-warning"></i> Edit Pertanyaan FAQ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="edit_question">Pertanyaan <span
                                class="text-danger">*</span></label>
                        <input type="text" id="edit_question" name="question"
                            class="form-control @error('question') is-invalid @enderror"
                            value="{{ old('_modal') === 'edit_faq' ? old('question') : '' }}" required>
                        @error('question')
                            @if (old('_modal') === 'edit_faq')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="edit_answer">Jawaban <span class="text-danger">*</span></label>
                        <textarea id="edit_answer" name="answer" rows="4" class="form-control @error('answer') is-invalid @enderror"
                            required>{{ old('_modal') === 'edit_faq' ? old('answer') : '' }}</textarea>
                        @error('answer')
                            @if (old('_modal') === 'edit_faq')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="edit_order">Nomor Urutan Tampil <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="edit_order" name="order" min="0"
                                class="form-control @error('order') is-invalid @enderror"
                                value="{{ old('_modal') === 'edit_faq' ? old('order') : '' }}" required>
                            @error('order')
                                @if (old('_modal') === 'edit_faq')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active"
                                    value="1">
                                <label class="form-check-label fw-semibold" for="edit_is_active">Aktif di Landing
                                    Page</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus FAQ -->
    <div class="modal fade" id="modalHapusFaq" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form id="formHapusFaq" action="" method="POST" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bx bx-trash me-1"></i> Hapus FAQ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bx bx-error-circle text-danger display-4 mb-2"></i>
                    <p class="mb-1">Apakah Anda yakin ingin menghapus pertanyaan ini dari FAQ Landing Page?</p>
                    <strong class="d-block text-heading text-break" id="hapus_question_text"></strong>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit FAQ Modal Setup
            const formEdit = document.getElementById('formEditFaq');
            const editQuestion = document.getElementById('edit_question');
            const editAnswer = document.getElementById('edit_answer');
            const editOrder = document.getElementById('edit_order');
            const editIsActive = document.getElementById('edit_is_active');
            const editActionFallback = document.getElementById('edit_action_fallback');

            document.querySelectorAll('.btn-edit-faq').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    const question = this.getAttribute('data-question');
                    const answer = this.getAttribute('data-answer');
                    const order = this.getAttribute('data-order');
                    const isActive = this.getAttribute('data-active') === '1';

                    if (formEdit) {
                        formEdit.action = action;
                        if (editActionFallback) editActionFallback.value = action;
                    }
                    if (editQuestion) editQuestion.value = question || '';
                    if (editAnswer) editAnswer.value = answer || '';
                    if (editOrder) editOrder.value = order || '';
                    if (editIsActive) editIsActive.checked = isActive;
                });
            });

            // Delete FAQ Modal Setup
            const formHapus = document.getElementById('formHapusFaq');
            const textHapusQuestion = document.getElementById('hapus_question_text');

            document.querySelectorAll('.btn-delete-faq').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    const question = this.getAttribute('data-question');

                    if (formHapus) formHapus.action = action;
                    if (textHapusQuestion) textHapusQuestion.textContent = `"${question}"`;
                });
            });

            // Toggle Status Switch AJAX
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            document.querySelectorAll('.toggle-status-switch').forEach(function(switchEl) {
                switchEl.addEventListener('change', function() {
                    const currentSwitch = this;
                    const action = currentSwitch.getAttribute('data-action');
                    const badgeEl = currentSwitch.closest('td')?.querySelector('.status-badge');
                    const isChecked = currentSwitch.checked;

                    currentSwitch.disabled = true;

                    fetch(action, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Gagal memperbarui status');
                            return response.json();
                        })
                        .then(data => {
                            currentSwitch.disabled = false;
                            if (data.success) {
                                if (badgeEl) {
                                    if (data.is_active) {
                                        badgeEl.textContent = 'Aktif';
                                        badgeEl.className =
                                            'badge bg-label-success status-badge';
                                    } else {
                                        badgeEl.textContent = 'Non-Aktif';
                                        badgeEl.className =
                                            'badge bg-label-secondary status-badge';
                                    }
                                }
                            } else {
                                currentSwitch.checked = !isChecked;
                            }
                        })
                        .catch(error => {
                            currentSwitch.disabled = false;
                            currentSwitch.checked = !isChecked;
                            alert('Terjadi kesalahan saat memperbarui status FAQ.');
                        });
                });
            });

            // Auto reopen modal on validation failure
            @if (isset($errors) && $errors->any())
                @if (old('_modal') === 'create_faq')
                    const modalTambahEl = document.getElementById('modalTambahFaq');
                    if (modalTambahEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalTambahEl).show();
                    }
                @elseif (old('_modal') === 'edit_faq')
                    const prevAction = '{{ old('_action') }}';
                    if (formEdit && prevAction) {
                        formEdit.action = prevAction;
                    }
                    const modalEditEl = document.getElementById('modalEditFaq');
                    if (modalEditEl && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(modalEditEl).show();
                    }
                @endif
            @endif
        });
    </script>
@endpush
