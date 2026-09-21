<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Services\GreedySchedulingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Tampilkan jadwal kunjungan gym berdasarkan tanggal dan time slot.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isMember = $user->hasRole('Member');

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        // Ambil seluruh time slot aktif
        $activeSlots = TimeSlot::active()->orderBy('start_time')->get();

        if ($isMember) {
            $memberId = $user->member?->id;

            $mySchedules = Schedule::with(['timeSlot', 'reservation.membership.product'])
                ->where('member_id', $memberId)
                ->orderByDesc('scheduled_date')
                ->paginate(15);

            return view('schedule.index', [
                'isMember' => true,
                'date' => $date,
                'mySchedules' => $mySchedules,
                'activeSlots' => $activeSlots,
            ]);
        }

        // Untuk Pengelola / Admin / Manager:
        // Ambil seluruh jadwal pada tanggal terpilih
        $daySchedules = Schedule::with(['member.user', 'timeSlot', 'reservation.membership.product'])
            ->whereDate('scheduled_date', $date)
            ->get();

        // Hitung metrik ringkasan hari tersebut
        $summary = [
            'total_scheduled' => $daySchedules->where('status', Schedule::STATUS_SCHEDULED)->count(),
            'total_attended' => $daySchedules->where('status', Schedule::STATUS_ATTENDED)->count(),
            'total_cancelled' => $daySchedules->where('status', Schedule::STATUS_CANCELLED)->count(),
            'total_no_show' => $daySchedules->where('status', Schedule::STATUS_NO_SHOW)->count(),
            'total_visits' => $daySchedules->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])->count(),
            'total_capacity' => $activeSlots->sum('capacity'),
        ];

        // Hitung reservasi pending pada tanggal tersebut yang belum dijadwalkan
        $pendingReservationsCount = Reservation::whereDate('visit_date', $date)
            ->where('status', Reservation::STATUS_PENDING)
            ->count();

        // Kelompokkan data per Time Slot
        $slotData = $activeSlots->map(function (TimeSlot $slot) use ($daySchedules) {
            $slotSchedules = $daySchedules->where('time_slot_id', $slot->id);
            $occupied = $slotSchedules->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])->count();
            $remaining = max(0, $slot->capacity - $occupied);
            $percentage = $slot->capacity > 0 ? round(($occupied / $slot->capacity) * 100, 1) : 0;

            return [
                'slot' => $slot,
                'schedules' => $slotSchedules,
                'occupied' => $occupied,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'is_full' => $remaining <= 0,
            ];
        });

        return view('schedule.index', [
            'isMember' => false,
            'date' => $date,
            'summary' => $summary,
            'pendingReservationsCount' => $pendingReservationsCount,
            'slotData' => $slotData,
            'activeSlots' => $activeSlots,
        ]);
    }

    /**
     * Jalankan optimasi batch Algoritma Greedy untuk seluruh reservasi pending pada tanggal tertentu.
     */
    public function optimize(Request $request, GreedySchedulingService $service): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $result = $service->optimizeDate($date);

        if ($result['total_pending'] === 0) {
            return redirect()->route('schedules.index', ['date' => $date])
                ->with('info', "Tidak ada antrean reservasi berstatus Pending untuk tanggal {$date}.");
        }

        $message = "Optimasi Algoritma Greedy Selesai! Berhasil mengalokasikan {$result['scheduled_count']} reservasi.";
        if ($result['failed_count'] > 0) {
            $message .= " {$result['failed_count']} reservasi gagal dialokasikan karena seluruh kapasitas time slot penuh.";
        }

        return redirect()->route('schedules.index', ['date' => $date])
            ->with('success', $message);
    }

    /**
     * Perbarui status kehadiran jadwal kunjungan (Attended, No Show, Cancelled).
     */
    public function updateStatus(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,attended,cancelled,no_show'],
        ]);

        $newStatus = $validated['status'];
        $schedule->update(['status' => $newStatus]);

        // Sinkronisasi status reservasi terkait
        if ($schedule->reservation) {
            if ($newStatus === Schedule::STATUS_ATTENDED) {
                $schedule->reservation->update(['status' => Reservation::STATUS_COMPLETED]);
            } elseif ($newStatus === Schedule::STATUS_CANCELLED) {
                $schedule->reservation->update(['status' => Reservation::STATUS_CANCELLED]);
            } elseif ($newStatus === Schedule::STATUS_SCHEDULED) {
                $schedule->reservation->update(['status' => Reservation::STATUS_SCHEDULED]);
            }
        }

        // Sinkronisasi data Attendance Check-in
        if ($newStatus === Schedule::STATUS_ATTENDED) {
            $existingAtt = Attendance::where('member_id', $schedule->member_id)
                ->whereDate('date', $schedule->scheduled_date)
                ->first();
            if (! $existingAtt) {
                Attendance::create([
                    'member_id' => $schedule->member_id,
                    'membership_id' => $schedule->reservation?->membership_id ?? $schedule->member->activeMembership()?->id,
                    'reservation_id' => $schedule->reservation_id,
                    'date' => $schedule->scheduled_date->toDateString(),
                    'check_in_at' => now(),
                    'status' => Attendance::STATUS_CHECKED_IN,
                    'scan_method' => 'manual',
                    'notes' => 'Presensi via Halaman Kunjungan',
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $label = match ($newStatus) {
            Schedule::STATUS_ATTENDED => 'Check-in (Hadir)',
            Schedule::STATUS_CANCELLED => 'Dibatalkan',
            Schedule::STATUS_NO_SHOW => 'Tidak Hadir (No Show)',
            default => 'Terjadwal',
        };

        return redirect()->back()
            ->with('success', "Status jadwal {$schedule->schedule_code} berhasil diperbarui menjadi {$label}.");
    }
}
