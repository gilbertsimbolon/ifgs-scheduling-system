<!-- Banner Informasi Jam Operasional Resmi IFGS (Dinamis dari TimeSlot) -->
<div class="card mb-4 border-0 shadow-sm bg-label-primary">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="mb-0 fw-bold text-primary d-flex align-items-center">
                <i class="bx bx-time-five me-2 fs-5"></i> Jam Operasional Gym Resmi
            </h6>
            @if (auth()->user()->hasRole('Admin/Manager'))
                <a href="{{ route('time-slots.index') }}"
                    class="badge bg-primary text-white text-decoration-none py-2 px-3 d-inline-flex align-items-center" title="Kelola Jadwal Operasional">
                    <i class="bx bx-cog me-1"></i> Kelola Jadwal Operasional
                </a>
            @else
                <a href="{{ route('reservations.index') }}"
                    class="badge bg-primary text-white text-decoration-none py-2 px-3 d-inline-flex align-items-center" title="Reservasi Kunjungan">
                    <i class="bx bx-calendar-plus me-1"></i> Reservasi Kunjungan
                </a>
            @endif
        </div>
        <div class="row align-items-center g-3">
            @foreach ($operationalSlots as $slot)
                @php
                    $color = match ($slot->category) {
                        'aerobic_zumba' => 'danger',
                        'fitness' => 'primary',
                        default => 'info',
                    };
                    $icon = match ($slot->category) {
                        'aerobic_zumba' => 'bx-body',
                        'fitness' => 'bx-dumbbell',
                        default => 'bx-time-five',
                    };
                    $formattedTime =
                        \Carbon\Carbon::parse($slot->start_time)->format('H.i') .
                        ' - ' .
                        \Carbon\Carbon::parse($slot->end_time)->format('H.i');
                @endphp
                <div class="col-12 col-sm-6 col-lg">
                    @if (auth()->user()->hasRole('Admin/Manager'))
                        <a href="{{ route('time-slots.index') }}" class="d-flex align-items-center text-decoration-none"
                            title="Kelola Jadwal {{ $slot->name }}">
                        @else
                            <div class="d-flex align-items-center">
                    @endif
                    <div class="avatar avatar-md me-3 flex-shrink-0">
                        <span class="avatar-initial rounded bg-{{ $color }} text-white">
                            <i class="bx {{ $icon }} fs-4"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-{{ $color }}">{{ strtoupper($slot->name) }}</h6>
                        <span class="fw-semibold text-heading">{{ $slot->days ?? 'Senin s/d Sabtu' }}</span>
                        <div>
                            <span
                                class="badge bg-{{ $color }} text-white font-monospace mt-1">{{ $formattedTime }}</span>
                        </div>
                    </div>
                    @if (auth()->user()->hasRole('Admin/Manager'))
                        </a>
                    @else
                </div>
            @endif
        </div>
        @endforeach

        <!-- Card Hari Libur -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md me-3 flex-shrink-0">
                    <span class="avatar-initial rounded bg-secondary text-white">
                        <i class="bx bx-calendar-x fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-secondary">HARI LIBUR</h6>
                    <span class="fw-semibold text-danger">Minggu & Tanggal Merah</span>
                    <div>
                        <span class="badge bg-label-danger mt-1">TUTUP OPERASIONAL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
