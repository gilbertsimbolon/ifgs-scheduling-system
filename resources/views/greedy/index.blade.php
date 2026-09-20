@extends('layouts.app')

@section('title', 'Greedy - Manajemen Kuota & Distribusi Operasional')

@section('content')
    <div class="container-xxl flex-grow-1">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible my-3" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible my-3" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible my-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bx bx-error-circle me-1"></i>
                    <strong>Terdapat kesalahan validasi:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Section: Konfigurasi Kuota Reservasi vs Walk-in (Dinamis Berdasarkan Time Slot Layanan) -->
        <div class="row g-4 mb-4 mt-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bx bx-slider-alt text-primary me-2"></i> Pengaturan Kuota Reservasi per Sesi Layanan
                        </h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            @foreach ($slotsData as $data)
                                @php
                                    $slot = $data['slot'];
                                    $cap = $slot->capacity;
                                    $resQuota = $data['reservation_quota'];
                                    $walkinQuota = $data['walkin_quota'];
                                    $resPercent = $cap > 0 ? round(($resQuota / $cap) * 100) : 0;
                                    $walkinPercent = 100 - $resPercent;
                                @endphp
                                <div class="col-md-6">
                                    <div class="card border shadow-none bg-lighter h-100">
                                        <div class="card-body">
                                            <!-- Sesi Header -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge {{ $slot->category === 'aerobic_zumba' ? 'bg-info' : 'bg-primary' }} p-2">
                                                        <i
                                                            class="bx {{ $slot->category === 'aerobic_zumba' ? 'bx-run' : 'bx-dumbbell' }} fs-5"></i>
                                                    </span>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">{{ $slot->name }}</h6>
                                                        <small class="text-muted">
                                                            <i class="bx bx-time-five me-1"></i> {{ $slot->time_range }}
                                                            WITA &bull; {{ $slot->days }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <span
                                                    class="badge {{ $slot->statusBadgeClass }}">{{ $slot->statusLabel }}</span>
                                            </div>

                                            <!-- Kapasitas vs Kuota Progress Bar -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                                    <span class="fw-semibold text-primary">
                                                        <i class="bx bx-calendar me-1"></i> Kuota Reservasi:
                                                        {{ $resQuota }} org ({{ $resPercent }}%)
                                                    </span>
                                                    <span class="fw-semibold text-warning">
                                                        <i class="bx bx-walk me-1"></i> Cadangan Walk-in:
                                                        {{ $walkinQuota }} org ({{ $walkinPercent }}%)
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 12px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: {{ $resPercent }}%"
                                                        aria-valuenow="{{ $resPercent }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        title="Kuota Reservasi: {{ $resQuota }} org"></div>
                                                    <div class="progress-bar bg-warning" role="progressbar"
                                                        style="width: {{ $walkinPercent }}%"
                                                        aria-valuenow="{{ $walkinPercent }}" aria-valuemin="0"
                                                        aria-valuemax="100" data-bs-toggle="tooltip"
                                                        title="Cadangan Walk-in: {{ $walkinQuota }} org"></div>
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between align-items-center small text-muted mt-1">
                                                    <span>0 org</span>
                                                    <span>Total Kapasitas: <strong>{{ $cap }}
                                                            orang</strong></span>
                                                </div>
                                            </div>

                                            <!-- Form Ubah Kuota -->
                                            <form action="{{ route('greedy.update-quota', $slot->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-7">
                                                        <label class="form-label small fw-semibold text-dark mb-1">
                                                            Batas Kuota Reservasi (org):
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i
                                                                    class="bx bx-user-check"></i></span>
                                                            <input type="number" name="reservation_quota"
                                                                class="form-control"
                                                                value="{{ $slot->reservation_quota ?? $resQuota }}"
                                                                min="0" max="{{ $cap }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-5">
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="bx bx-save me-1"></i> Simpan
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                                    <i class="bx bx-info-circle me-1"></i> Sisa {{ $cap }} -
                                                    kuota reservasi akan otomatis dicadangkan untuk pengunjung walk-in.
                                                </small>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Monitoring Keterisian Realtime & Distribusi Beban Algoritma Greedy -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div
                        class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bx bx-line-chart text-primary me-2"></i> Distribusi Beban Sesi
                        </h5>
                        <span class="badge bg-label-info">
                            <i class="bx bx-check-shield me-1"></i> Load Balancing Active
                        </span>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-4">
                            @foreach ($slotsData as $data)
                                @php
                                    $slot = $data['slot'];
                                    $occupied = $data['occupied'];
                                    $resQuota = $data['reservation_quota'];
                                    $remaining = $data['remaining'];
                                    $rate = $data['occupancy_rate'];
                                    $badgeColor = $rate >= 100 ? 'danger' : ($rate >= 75 ? 'warning' : 'success');
                                @endphp
                                <div class="col-md-6">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $slot->name }}</h6>
                                                <span class="text-muted small">{{ $slot->time_range }} WITA</span>
                                            </div>
                                            <span class="badge bg-label-{{ $badgeColor }}">
                                                {{ $data['is_full'] ? 'Kuota Reservasi Penuh' : 'Tersedia (' . $remaining . ' slot)' }}
                                            </span>
                                        </div>

                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                                <span class="text-muted">Keterisian Reservasi Online:</span>
                                                <strong class="text-{{ $badgeColor }}">{{ $occupied }} /
                                                    {{ $resQuota }} org ({{ $rate }}%)</strong>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $badgeColor }}" role="progressbar"
                                                    style="width: {{ $rate }}%"
                                                    aria-valuenow="{{ $rate }}" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex justify-content-between align-items-center small text-muted pt-2 border-top">
                                            <span>Batas Reservasi: <strong>{{ $resQuota }} org</strong></span>
                                            <span>Cadangan Walk-in: <strong>{{ $data['walkin_quota'] }} org</strong></span>
                                            <span>Kapasitas Total: <strong>{{ $slot->capacity }} org</strong></span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
