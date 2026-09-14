<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard utama IFGS Scheduling System.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today()->format('Y-m-d');
        $user = auth()->user();
        $isMember = $user->hasRole('Member');

        // Jika user adalah Member, tampilkan ringkasan pribadi
        if ($isMember) {
            $member = $user->member;
            $activeMembership = $member ? $member->activeMembership() : null;

            $myReservationsCount = $member ? Reservation::where('member_id', $member->id)->count() : 0;
            $myUpcomingSchedules = $member ? Schedule::with(['timeSlot', 'reservation'])
                ->where('member_id', $member->id)
                ->whereDate('scheduled_date', '>=', $today)
                ->where('status', Schedule::STATUS_SCHEDULED)
                ->orderBy('scheduled_date')
                ->take(5)
                ->get() : collect();

            $myRecentVisits = $member ? Schedule::with('timeSlot')
                ->where('member_id', $member->id)
                ->where('status', Schedule::STATUS_ATTENDED)
                ->orderByDesc('scheduled_date')
                ->take(5)
                ->get() : collect();

            return view('dashboard.index', [
                'isMember' => true,
                'member' => $member,
                'activeMembership' => $activeMembership,
                'myReservationsCount' => $myReservationsCount,
                'myUpcomingSchedules' => $myUpcomingSchedules,
                'myRecentVisits' => $myRecentVisits,
            ]);
        }

        // Metrik Utama Gym untuk Admin / Pengelola
        $totalMembers = Member::count();
        $activeMembers = Member::whereHas('user', function ($q) {
            $q->where('status', User::STATUS_ACTIVE);
        })->count();

        $activeMemberships = Membership::where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', $today)
            ->count();

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

        return view('dashboard.index', [
            'isMember' => false,
            'today' => $today,
            'totalMembers' => $totalMembers,
            'activeMembers' => $activeMembers,
            'activeMemberships' => $activeMemberships,
            'todayReservationsCount' => $todayReservationsCount,
            'todayPendingReservations' => $todayPendingReservations,
            'todayScheduledReservations' => $todayScheduledReservations,
            'todayScheduledCount' => $todayScheduledCount,
            'todayAttendedCount' => $todayAttendedCount,
            'todayTotalVisits' => $todayTotalVisits,
            'totalGymCapacity' => $totalGymCapacity,
            'slotOccupancies' => $slotOccupancies,
            'recentSchedules' => $recentSchedules,
        ]);
    }
}
