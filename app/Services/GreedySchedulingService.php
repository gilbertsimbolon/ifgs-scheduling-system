<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GreedySchedulingService
{
    /**
     * Jadwalkan reservasi tunggal menggunakan Algoritma Greedy.
     *
     * @param  Reservation  $reservation  Reservasi yang akan dijadwalkan
     * @param  int|null  $preferredSlotId  ID time slot preferensi (opsional)
     * @return array{success: bool, message: string, time_slot?: TimeSlot, schedule?: Schedule, metrics?: array}
     */
    public function scheduleReservation(Reservation $reservation, ?int $preferredSlotId = null): array
    {
        $visitDate = $reservation->visit_date->format('Y-m-d');

        // 1. Dapatkan candidate slots (slot aktif)
        $activeSlots = TimeSlot::active()->orderBy('start_time')->get();

        if ($activeSlots->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada Time Slot aktif yang tersedia di sistem.',
            ];
        }

        // 2. Hitung occupancy dan sisa kapasitas saat ini untuk tanggal kunjungan
        $slotOccupancies = $this->calculateOccupanciesForDate($visitDate, $activeSlots);

        // 3. Evaluasi candidate slots yang masih memiliki sisa kuota reservasi
        $availableCandidates = $activeSlots->filter(function (TimeSlot $slot) use ($slotOccupancies) {
            $occupied = $slotOccupancies[$slot->id] ?? 0;

            return $occupied < $slot->effective_reservation_quota;
        });

        if ($availableCandidates->isEmpty()) {
            return [
                'success' => false,
                'message' => "Seluruh kuota reservasi Time Slot pada tanggal {$visitDate} sudah penuh.",
            ];
        }

        // 4. Greedy Selection:
        // Jika terdapat slot preferensi dan slot tersebut masih memiliki kapasitas:
        $selectedSlot = null;
        $selectionReason = '';

        if ($preferredSlotId) {
            $preferredSlot = $availableCandidates->firstWhere('id', $preferredSlotId);
            if ($preferredSlot) {
                $selectedSlot = $preferredSlot;
                $selectionReason = 'Sesuai preferensi member (kapasitas tersedia).';
            }
        }

        // Jika tidak ada preferensi atau preferensi penuh, pilih berdasarkan kriteria Greedy:
        // Kriteria: Pilih slot dengan occupancy rate terendah (beban paling renggang)
        // Tie-breaker deterministik: Waktu mulai lebih awal (start_time ASC, id ASC)
        if (! $selectedSlot) {
            $selectedSlot = $this->selectBestGreedySlot($availableCandidates, $slotOccupancies);
            $selectionReason = 'Dipilih secara optimal oleh Algoritma Greedy untuk meratakan distribusi beban kunjungan gym.';
        }

        // 5. Eksekusi penetapan jadwal (Capacity Constraint & Atomic Transaction)
        return DB::transaction(function () use ($reservation, $selectedSlot, $visitDate, $selectionReason, $slotOccupancies) {
            // Update reservasi
            $reservation->update([
                'time_slot_id' => $selectedSlot->id,
                'status' => Reservation::STATUS_SCHEDULED,
            ]);

            // Buat atau perbarui jadwal kunjungan (Schedule)
            $schedule = Schedule::updateOrCreate(
                ['reservation_id' => $reservation->id],
                [
                    'schedule_code' => Schedule::generateScheduleCode(),
                    'member_id' => $reservation->member_id,
                    'time_slot_id' => $selectedSlot->id,
                    'scheduled_date' => $visitDate,
                    'status' => Schedule::STATUS_SCHEDULED,
                    'notes' => $selectionReason,
                ]
            );

            $occupancyBefore = $slotOccupancies[$selectedSlot->id] ?? 0;
            $occupancyAfter = $occupancyBefore + 1;

            return [
                'success' => true,
                'message' => "Reservasi berhasil dijadwalkan pada {$selectedSlot->name} ({$selectedSlot->time_range}).",
                'time_slot' => $selectedSlot,
                'schedule' => $schedule,
                'metrics' => [
                    'reason' => $selectionReason,
                    'slot_name' => $selectedSlot->name,
                    'slot_capacity' => $selectedSlot->capacity,
                    'reservation_quota' => $selectedSlot->effective_reservation_quota,
                    'walkin_quota' => $selectedSlot->walkin_quota,
                    'occupancy_before' => $occupancyBefore,
                    'occupancy_after' => $occupancyAfter,
                    'remaining_capacity' => max(0, $selectedSlot->effective_reservation_quota - $occupancyAfter),
                ],
            ];
        });
    }

    /**
     * Pilih candidate slot terbaik berdasarkan Kriteria Greedy (Load Balancing / Least Occupied).
     *
     * @param  Collection<int, TimeSlot>  $candidates
     * @param  array<int, int>  $occupancies
     */
    public function selectBestGreedySlot(Collection $candidates, array $occupancies): TimeSlot
    {
        return $candidates->sort(function (TimeSlot $a, TimeSlot $b) use ($occupancies) {
            $occA = $occupancies[$a->id] ?? 0;
            $occB = $occupancies[$b->id] ?? 0;

            // Kriteria 1: Rasio kepadatan terhadap kuota reservasi (occupancy rate = terisi / kuota reservasi)
            $quotaA = $a->effective_reservation_quota;
            $quotaB = $b->effective_reservation_quota;
            $rateA = $quotaA > 0 ? ($occA / $quotaA) : 1.0;
            $rateB = $quotaB > 0 ? ($occB / $quotaB) : 1.0;

            if ($rateA !== $rateB) {
                return $rateA <=> $rateB; // Terendah diprioritaskan
            }

            // Kriteria 2: Jumlah mutlak pengunjung saat ini
            if ($occA !== $occB) {
                return $occA <=> $occB;
            }

            // Tie-breaker 1: Waktu mulai lebih awal (deterministic)
            if ($a->start_time !== $b->start_time) {
                return strcmp($a->start_time, $b->start_time);
            }

            // Tie-breaker 2: ID lebih kecil (deterministic)
            return $a->id <=> $b->id;
        })->first();
    }

    /**
     * Jalankan optimasi batch Algoritma Greedy untuk seluruh reservasi pending pada suatu tanggal.
     *
     * @param  string  $date  Format Y-m-d
     * @return array{total_pending: int, scheduled_count: int, failed_count: int, details: array, distribution_before: array, distribution_after: array}
     */
    public function optimizeDate(string $date): array
    {
        $pendingReservations = Reservation::whereDate('visit_date', $date)
            ->where('status', Reservation::STATUS_PENDING)
            ->orderBy('id')
            ->get();

        $activeSlots = TimeSlot::active()->orderBy('start_time')->get();
        $slotOccupancies = $this->calculateOccupanciesForDate($date, $activeSlots);

        $distributionBefore = [];
        foreach ($activeSlots as $slot) {
            $distributionBefore[$slot->id] = [
                'slot_name' => $slot->name,
                'time_range' => $slot->time_range,
                'capacity' => $slot->capacity,
                'reservation_quota' => $slot->effective_reservation_quota,
                'walkin_quota' => $slot->walkin_quota,
                'count' => $slotOccupancies[$slot->id] ?? 0,
            ];
        }

        $scheduledCount = 0;
        $failedCount = 0;
        $details = [];

        foreach ($pendingReservations as $reservation) {
            // Filter candidate yang kapasitas reservasi masih cukup dengan occupancy dinamis
            $candidates = $activeSlots->filter(function (TimeSlot $slot) use ($slotOccupancies) {
                return ($slotOccupancies[$slot->id] ?? 0) < $slot->effective_reservation_quota;
            });

            if ($candidates->isEmpty()) {
                $failedCount++;
                $details[] = [
                    'reservation_code' => $reservation->code,
                    'member_name' => $reservation->member->user->name ?? '-',
                    'status' => 'Gagal: Kuota reservasi seluruh slot sudah penuh',
                ];

                continue;
            }

            // Pilih slot terbaik berdasarkan greedy
            $bestSlot = $this->selectBestGreedySlot($candidates, $slotOccupancies);

            DB::transaction(function () use ($reservation, $bestSlot, $date) {
                $reservation->update([
                    'time_slot_id' => $bestSlot->id,
                    'status' => Reservation::STATUS_SCHEDULED,
                ]);

                Schedule::updateOrCreate(
                    ['reservation_id' => $reservation->id],
                    [
                        'schedule_code' => Schedule::generateScheduleCode(),
                        'member_id' => $reservation->member_id,
                        'time_slot_id' => $bestSlot->id,
                        'scheduled_date' => $date,
                        'status' => Schedule::STATUS_SCHEDULED,
                        'notes' => 'Dijadwalkan otomatis melalui Optimasi Batch Algoritma Greedy.',
                    ]
                );
            });

            // Update occupancy lokal untuk iterasi berikutnya (Greedy State Step-by-Step)
            $slotOccupancies[$bestSlot->id] = ($slotOccupancies[$bestSlot->id] ?? 0) + 1;
            $scheduledCount++;

            $details[] = [
                'reservation_code' => $reservation->code,
                'member_name' => $reservation->member->user->name ?? '-',
                'status' => "Berhasil dialokasikan ke {$bestSlot->name} ({$bestSlot->time_range})",
            ];
        }

        $distributionAfter = [];
        foreach ($activeSlots as $slot) {
            $distributionAfter[$slot->id] = [
                'slot_name' => $slot->name,
                'time_range' => $slot->time_range,
                'capacity' => $slot->capacity,
                'reservation_quota' => $slot->effective_reservation_quota,
                'walkin_quota' => $slot->walkin_quota,
                'count' => $slotOccupancies[$slot->id] ?? 0,
            ];
        }

        return [
            'total_pending' => $pendingReservations->count(),
            'scheduled_count' => $scheduledCount,
            'failed_count' => $failedCount,
            'details' => $details,
            'distribution_before' => $distributionBefore,
            'distribution_after' => $distributionAfter,
        ];
    }

    /**
     * Hitung jumlah kunjungan yang terkonfirmasi per time slot untuk tanggal tertentu.
     *
     * @param  Collection<int, TimeSlot>  $slots
     * @return array<int, int>
     */
    public function calculateOccupanciesForDate(string $date, Collection $slots): array
    {
        $slotIds = $slots->pluck('id')->all();

        $counts = Schedule::whereDate('scheduled_date', $date)
            ->whereIn('time_slot_id', $slotIds)
            ->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])
            ->select('time_slot_id', DB::raw('count(*) as aggregate'))
            ->groupBy('time_slot_id')
            ->pluck('aggregate', 'time_slot_id')
            ->all();

        $occupancies = [];
        foreach ($slotIds as $id) {
            $occupancies[$id] = (int) ($counts[$id] ?? 0);
        }

        return $occupancies;
    }
}
