<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard utama IFGS Scheduling System.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        // Member dan Trainer tidak masuk ke admin panel / dashboard
        // Member diarahkan ke Member Mobile Portal
        if ($user->hasRole('Member')) {
            return redirect()->route('member.index');
        }

        // Role selain Admin/Manager dan Kasir tidak masuk ke admin panel
        if (! $user->hasAnyRole(['Admin/Manager', 'Kasir'])) {
            return redirect()->route('home');
        }

        $today = Carbon::today()->format('Y-m-d');

        // Metrik Utama Gym untuk Admin / Pengelola
        $totalMembers = Member::count();
        $activeMembers = Member::whereHas('user', function ($q) {
            $q->where('status', User::STATUS_ACTIVE);
        })->count();

        $activeMemberships = Membership::where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', $today)
            ->count();

        // Metrik Ringkasan Membership & Pendapatan Bulanan (Dipindahkan dari Halaman Membership)
        $totalMembershipTransactions = Membership::count();
        $expiredMemberships = Membership::where('status', Membership::STATUS_EXPIRED)
            ->orWhere(function ($q) use ($today) {
                $q->where('status', Membership::STATUS_ACTIVE)
                    ->whereDate('end_date', '<', $today);
            })->count();
        $monthlyRevenue = Membership::where('status', '!=', Membership::STATUS_CANCELLED)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('price');
        $currentMonthLabel = Carbon::now()->translatedFormat('F Y');

        // Metrik Validasi Transaksi Membership (Dipindahkan dari Halaman Transaksi Membership)
        $txMetrics = [
            'pending' => Membership::where('status', Membership::STATUS_PENDING)->count(),
            'approved' => Membership::where('status', Membership::STATUS_ACTIVE)->count(),
            'rejected' => Membership::whereIn('status', [Membership::STATUS_REJECTED, Membership::STATUS_CANCELLED])->count(),
            'total_revenue' => Membership::where('status', Membership::STATUS_ACTIVE)->sum('price'),
        ];

        // Reservasi Hari Ini
        $todayReservationsCount = Reservation::whereDate('visit_date', $today)->count();
        $todayPendingReservations = Reservation::whereDate('visit_date', $today)
            ->where('status', Reservation::STATUS_PENDING)
            ->count();
        $todayScheduledReservations = Reservation::whereDate('visit_date', $today)
            ->where('status', Reservation::STATUS_SCHEDULED)
            ->count();

        // Jadwal & Kunjungan Hari Ini
        $todaySchedules = Schedule::with(['member.user', 'timeSlot', 'reservation.membership.product'])
            ->whereDate('scheduled_date', $today)
            ->get();

        $todayScheduledCount = $todaySchedules->where('status', Schedule::STATUS_SCHEDULED)->count();
        $todayAttendedCount = $todaySchedules->where('status', Schedule::STATUS_ATTENDED)->count();
        $todayTotalVisits = $todaySchedules->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])->count();

        // Okupansi Slot Hari Ini
        $activeSlots = TimeSlot::active()->orderBy('start_time')->get();
        $totalGymCapacity = $activeSlots->sum('capacity');

        $slotOccupancies = $activeSlots->map(function (TimeSlot $slot) use ($todaySchedules) {
            $slotSchedules = $todaySchedules->where('time_slot_id', $slot->id);
            $occupied = $slotSchedules->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])->count();
            $remaining = max(0, $slot->capacity - $occupied);
            $percentage = $slot->capacity > 0 ? round(($occupied / $slot->capacity) * 100, 1) : 0;

            return [
                'slot' => $slot,
                'occupied' => $occupied,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'is_full' => $remaining <= 0,
            ];
        });

        // 5 Reservasi / Kunjungan Terbaru Hari Ini
        $recentSchedules = $todaySchedules->sortByDesc('id')->take(6);

        // Metrik Reservasi Sistem (Dipindahkan dari Halaman Reservasi)
        $reservationMetrics = [
            'total' => Reservation::count(),
            'scheduled' => Reservation::where('status', Reservation::STATUS_SCHEDULED)->count(),
            'pending' => Reservation::where('status', Reservation::STATUS_PENDING)->count(),
            'completed' => Reservation::where('status', Reservation::STATUS_COMPLETED)->count(),
            'cancelled' => Reservation::where('status', Reservation::STATUS_CANCELLED)->count(),
        ];

        return view('dashboard.index', [
            'isMember' => false,
            'today' => $today,
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'activeMemberships' => $activeMemberships,
            'totalMembershipTransactions' => $totalMembershipTransactions,
            'expiredMemberships' => $expiredMemberships,
            'monthlyRevenue' => $monthlyRevenue,
            'currentMonthLabel' => $currentMonthLabel,
            'todayReservationsCount' => $todayReservationsCount,
            'todayPendingReservations' => $todayPendingReservations,
            'todayScheduledReservations' => $todayScheduledReservations,
            'todayScheduledCount' => $todayScheduledCount,
            'todayAttendedCount' => $todayAttendedCount,
            'todayTotalVisits' => $todayTotalVisits,
            'totalGymCapacity' => $totalGymCapacity,
            'slotOccupancies' => $slotOccupancies,
            'recentSchedules' => $recentSchedules,
            'operationalSlots' => $activeSlots,
            'txMetrics' => $txMetrics,
            'reservationMetrics' => $reservationMetrics,
        ]);
    }
}
