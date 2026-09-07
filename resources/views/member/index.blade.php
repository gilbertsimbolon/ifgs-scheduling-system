@extends('layouts.app')

@section('title', 'Daftar Member')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Home /</span> Member
                </h5>
                <p class="text-muted mb-0">Kelola data member yang terdaftar pada sistem.</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary disabled" aria-disabled="true" title="Fitur tambah member segera hadir">
                    <i class="bx bx-plus me-1"></i> Tambah Member
                </button>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <!-- Tabel Member -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>No. HP</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($members as $member)
                            <tr>
                                <td>{{ $members->firstItem() + $loop->index }}</td>
                                <td>
                                    <span class="badge bg-label-secondary font-monospace">{{ $member->member_code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($member->user?->name ?? 'M', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-heading text-truncate" style="max-width: 220px;">
                                                {{ $member->user?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="{{ $member->phone ? '' : 'text-muted' }}">
                                        {{ $member->phone ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $member->user?->email ?? '-' }}</td>
                                <td>
                                    @if (($member->user?->status ?? '') === \App\Models\User::STATUS_ACTIVE)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">-</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100 py-3">
                                        <div class="mb-2">
                                            <i class="bx bx-group fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold mb-1">Belum ada member</h6>
                                        <span class="text-muted">Belum ada member yang terdaftar pada sistem.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">
                    @if ($members->total() > 0)
                        Menampilkan {{ $members->firstItem() }}–{{ $members->lastItem() }} dari {{ $members->total() }} member
                    @else
                        Tidak ada data member
                    @endif
                </small>
                @if ($members->hasPages())
                    {{ $members->onEachSide(1)->links('vendor.pagination.compact') }}
                @endif
            </div>
        </div>
    </div>
@endsection
