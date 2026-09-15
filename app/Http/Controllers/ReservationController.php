<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Services\GreedySchedulingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * Tampilkan daftar reservasi kunjungan.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isMember = $user->hasRole('Member');

        $query = Reservation::with(['member.user', 'membership.product', 'timeSlot', 'schedule']);

        // Jika user adalah Member, hanya tampilkan reservasi miliknya sendiri
        if ($isMember) {
            $member = $user->member;
            if (! $member) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('member_id', $member->id);
            }
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhereHas('member.user', function ($uq) use ($search) {
                        $uq->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter tanggal kunjungan
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        $reservations = $query->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Metrik
        $metricQuery = Reservation::query();
        if ($isMember && $user->member) {
            $metricQuery->where('member_id', $user->member->id);
        }

        $metrics = [
            'total' => (clone $metricQuery)->count(),
            'scheduled' => (clone $metricQuery)->where('status', Reservation::STATUS_SCHEDULED)->count(),
            'pending' => (clone $metricQuery)->where('status', Reservation::STATUS_PENDING)->count(),
            'completed' => (clone $metricQuery)->where('status', Reservation::STATUS_COMPLETED)->count(),
            'cancelled' => (clone $metricQuery)->where('status', Reservation::STATUS_CANCELLED)->count(),
        ];

        // Master data untuk modal form
        $timeSlots = TimeSlot::active()->orderBy('start_time')->get();

        $activeMembers = collect();
        if (! $isMember) {
            $activeMembers = Member::with(['user', 'memberships' => function ($q) {
                $q->where('status', Membership::STATUS_ACTIVE)
                    ->whereDate('end_date', '>=', now());
            }])->get()->filter(fn (Member $m) => $m->memberships->isNotEmpty());
        }

        $myActiveMembership = null;
        if ($isMember && $user->member) {
            $myActiveMembership = $user->member->activeMembership();
        }

        return view('reservation.index', compact(
            'reservations',
            'metrics',
            'timeSlots',
            'activeMembers',
            'isMember',
            'myActiveMembership'
        ));
    }

    /**
     * Simpan reservasi kunjungan baru dan alokasikan slot menggunakan Algoritma Greedy.
     */
    public function store(Request $request, GreedySchedulingService $greedyService): RedirectResponse
    {
        $user = auth()->user();
        $isMember = $user->hasRole('Member');

        $rules = [
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot_id' => ['nullable', 'exists:time_slots,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if (! $isMember) {
            $rules['member_id'] = ['required', 'exists:members,id'];
        }

        $validated = $request->validate($rules, [
            'visit_date.required' => 'Tanggal kunjungan wajib dipilih.',
            'visit_date.date' => 'Format tanggal kunjungan tidak valid.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lampau.',
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.exists' => 'Data member tidak ditemukan.',
            'time_slot_id.exists' => 'Time Slot yang dipilih tidak valid.',
        ]);

        // Tentukan member
        $memberId = $isMember ? $user->member?->id : (int) $validated['member_id'];

        if (! $memberId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda belum terdaftar sebagai member gym aktif. Silakan hubungi kasir/pengelola gym untuk berlangganan paket membership.');
        }

        $member = Member::findOrFail($memberId);
        $visitDate = Carbon::parse($validated['visit_date'])->format('Y-m-d');
        $dateObj = Carbon::parse($validated['visit_date']);
        $visitDate = $dateObj->format('Y-m-d');

        // Validasi operasional: Hari Minggu tutup
        if ($dateObj->isSunday()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Indo Fitness Gym Sport tutup pada hari Minggu sesuai ketentuan jadwal operasional.');
        }

        // 1. Validasi membership aktif pada tanggal kunjungan
        $validMembership = $member->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', $visitDate)
            ->whereDate('end_date', '>=', $visitDate)
            ->first();

        if (! $validMembership) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Member '{$member->user->name}' tidak memiliki paket membership aktif pada tanggal {$visitDate}.");
        }

        // 2. Validasi duplikat reservasi pada tanggal yang sama
        $duplicateReservation = Reservation::where('member_id', $member->id)
            ->whereDate('visit_date', $visitDate)
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_SCHEDULED])
            ->exists();

        if ($duplicateReservation) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Member '{$member->user->name}' sudah memiliki jadwal/reservasi kunjungan pada tanggal {$visitDate}.");
        }

        // 3. Buat record reservasi (Initial State)
        $reservation = Reservation::create([
            'code' => Reservation::generateCode(),
            'member_id' => $member->id,
            'membership_id' => $validMembership->id,
            'visit_date' => $visitDate,
            'time_slot_id' => $validated['time_slot_id'] ?? null,
            'status' => Reservation::STATUS_PENDING,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        // 4. Jalankan Algoritma Greedy untuk menentukan time slot & jadwal
        $scheduleResult = $greedyService->scheduleReservation(
            $reservation,
            $validated['time_slot_id'] ?? null
        );

        if ($scheduleResult['success']) {
            $msg = "Reservasi {$reservation->code} berhasil dijadwalkan! {$scheduleResult['message']}";

            return redirect()->route('reservations.index')->with('success', $msg);
        }

        // Jika seluruh slot penuh pada tanggal tersebut
        return redirect()->route('reservations.index')
            ->with('warning', "Reservasi dibuat ({$reservation->code}), namun jadwal gagal dialokasikan otomatis: {$scheduleResult['message']}");
    }

    /**
     * Batalkan reservasi kunjungan.
     */
    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = auth()->user();

        // Otorisasi: Member hanya boleh membatalkan reservasi miliknya
        if ($user->hasRole('Member') && $reservation->member_id !== $user->member?->id) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan reservasi ini.');
        }

        if (! $reservation->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'Reservasi ini tidak dapat dibatalkan karena sudah selesai atau masa kunjungannya telah berlalu.');
        }

        $reservation->update(['status' => Reservation::STATUS_CANCELLED]);

        if ($reservation->schedule) {
            $reservation->schedule->update(['status' => Schedule::STATUS_CANCELLED]);
        }

        return redirect()->route('reservations.index')
            ->with('success', "Reservasi {$reservation->code} berhasil dibatalkan.");
    }

    /**
     * AJAX endpoint: Cek ketersediaan time slot dan kapasitas pada tanggal tertentu.
     */
    public function availableSlots(Request $request, GreedySchedulingService $greedyService): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $request->date;
        $activeSlots = TimeSlot::active()->orderBy('start_time')->get();
        $occupancies = $greedyService->calculateOccupanciesForDate($date, $activeSlots);

        $data = $activeSlots->map(function (TimeSlot $slot) use ($occupancies) {
            $occupied = $occupancies[$slot->id] ?? 0;
            $remaining = max(0, $slot->capacity - $occupied);
            $isFull = $remaining <= 0;

            return [
                'id' => $slot->id,
                'name' => $slot->name,
                'time_range' => $slot->time_range,
                'capacity' => $slot->capacity,
                'occupied' => $occupied,
                'remaining' => $remaining,
                'is_full' => $isFull,
            ];
        });

        return response()->json([
            'date' => $date,
            'slots' => $data,
        ]);
    }
}
