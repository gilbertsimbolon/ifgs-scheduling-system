<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Services\GreedySchedulingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GreedyController extends Controller
{
    /**
     * Tampilkan halaman operasional manajemen Kuota & Algoritma Greedy.
     */
    public function index(Request $request, GreedySchedulingService $greedyService): View
    {
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->date)->format('Y-m-d')
            : now()->format('Y-m-d');

        $timeSlots = TimeSlot::active()->orderBy('start_time')->get();
        $occupancies = $greedyService->calculateOccupanciesForDate($selectedDate, $timeSlots);

        $slotsData = $timeSlots->map(function (TimeSlot $slot) use ($occupancies) {
            $occupied = $occupancies[$slot->id] ?? 0;
            $resQuota = $slot->effective_reservation_quota;
            $walkinQuota = $slot->walkin_quota;
            $remaining = max(0, $resQuota - $occupied);
            $occupancyRate = $resQuota > 0 ? round(($occupied / $resQuota) * 100, 1) : 100;

            return [
                'slot' => $slot,
                'occupied' => $occupied,
                'reservation_quota' => $resQuota,
                'walkin_quota' => $walkinQuota,
                'remaining' => $remaining,
                'occupancy_rate' => min(100, $occupancyRate),
                'is_full' => $remaining <= 0,
            ];
        });

        $totalCapacity = $timeSlots->sum('capacity');
        $totalReservationQuota = $slotsData->sum('reservation_quota');
        $totalWalkinQuota = $slotsData->sum('walkin_quota');
        $totalOccupied = $slotsData->sum('occupied');

        $pendingReservations = Reservation::whereDate('visit_date', $selectedDate)
            ->where('status', Reservation::STATUS_PENDING)
            ->with(['member.user', 'product'])
            ->orderBy('id')
            ->get();

        $confirmedSchedules = Schedule::whereDate('scheduled_date', $selectedDate)
            ->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])
            ->with(['member.user', 'timeSlot', 'reservation'])
            ->latest()
            ->take(15)
            ->get();

        $metrics = [
            'total_capacity' => $totalCapacity,
            'total_reservation_quota' => $totalReservationQuota,
            'total_walkin_quota' => $totalWalkinQuota,
            'total_occupied' => $totalOccupied,
            'pending_count' => $pendingReservations->count(),
            'confirmed_count' => $confirmedSchedules->count(),
        ];

        return view('greedy.index', compact(
            'selectedDate',
            'timeSlots',
            'slotsData',
            'metrics',
            'pendingReservations',
            'confirmedSchedules'
        ));
    }

    /**
     * Perbarui alokasi kuota reservasi online untuk suatu Time Slot.
     */
    public function updateQuota(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_quota' => [
                'required',
                'integer',
                'min:0',
                "max:{$timeSlot->capacity}",
            ],
        ], [
            'reservation_quota.required' => 'Kuota reservasi online wajib diisi.',
            'reservation_quota.integer' => 'Kuota reservasi harus berupa angka bulat.',
            'reservation_quota.min' => 'Kuota reservasi tidak boleh kurang dari 0.',
            'reservation_quota.max' => "Kuota reservasi tidak boleh melebihi kapasitas fisik ({$timeSlot->capacity} orang).",
        ]);

        $timeSlot->update([
            'reservation_quota' => $validated['reservation_quota'],
        ]);

        return redirect()->back()->with(
            'success',
            "Kuota reservasi online untuk '{$timeSlot->name}' berhasil diperbarui menjadi {$timeSlot->reservation_quota} orang (Cadangan Walk-in: {$timeSlot->walkin_quota} orang)."
        );
    }

    /**
     * Jalankan optimasi batch Algoritma Greedy untuk seluruh reservasi pending pada tanggal yang dipilih.
     */
    public function optimizeBatch(Request $request, GreedySchedulingService $greedyService): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ], [
            'date.required' => 'Tanggal optimasi wajib dipilih.',
            'date.date' => 'Format tanggal tidak valid.',
        ]);

        $result = $greedyService->optimizeDate($validated['date']);

        $message = "Optimasi Greedy selesai! {$result['scheduled_count']} reservasi berhasil dialokasikan secara optimal.";
        if ($result['failed_count'] > 0) {
            $message .= " ({$result['failed_count']} reservasi gagal karena kuota penuh).";
        }

        return redirect()->route('greedy.index', ['date' => $validated['date']])
            ->with('success', $message)
            ->with('optimization_result', $result);
    }
}
