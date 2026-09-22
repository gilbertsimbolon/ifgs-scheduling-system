<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan Halaman Utama (Landing Page) Indo Fitness Gym Sport®.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today()->format('Y-m-d');
        $activeProducts = Product::where('status', Product::STATUS_ACTIVE)->orderBy('price')->get();
        $activePaymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)->orderBy('name')->get();
        $operationalSlots = TimeSlot::active()->orderBy('start_time')->get();
        $activeTrainers = Trainer::active()->with('user')->get();

        $user = auth()->user();
        $member = null;
        $activeMembership = null;
        $pendingMembership = null;
        $myUpcomingSchedules = collect();
        $myRecentVisits = collect();
        $myReservations = collect();
        $canMakeReservation = false;
        $isDailyVisitOnly = false;

        $trainer = null;
        $trainerBookings = collect();
        $trainerMetrics = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'completed' => 0,
            'rejected' => 0,
        ];

        if ($user) {
            // Data untuk Member
            if ($user->hasRole('Member')) {
                $member = $user->member;
                if ($member) {
                    $activeMembership = $member->activeMembership();
                    $canMakeReservation = $member->canMakeReservation();
                    $isDailyVisitOnly = $member->hasOnlyDailyVisitMembership();
                    $pendingMembership = Membership::with(['product', 'paymentMethod', 'transaction'])
                        ->where('member_id', $member->id)
                        ->where('status', Membership::STATUS_PENDING)
                        ->latest()
                        ->first();

                    $myUpcomingSchedules = Schedule::with(['timeSlot', 'reservation.membership.product'])
                        ->where('member_id', $member->id)
                        ->whereDate('scheduled_date', '>=', $today)
                        ->orderBy('scheduled_date')
                        ->take(10)
                        ->get();

                    $myRecentVisits = Schedule::with('timeSlot')
                        ->where('member_id', $member->id)
                        ->where('status', Schedule::STATUS_ATTENDED)
                        ->latest('scheduled_date')
                        ->take(5)
                        ->get();

                    $myReservations = Reservation::with(['timeSlot', 'membership.product'])
                        ->where('member_id', $member->id)
                        ->latest()
                        ->take(5)
                        ->get();
                }
            }

            // Data untuk Trainer
            if ($user->hasRole('Trainer')) {
                $trainer = $user->trainer;
                if ($trainer) {
                    $trainerBookings = TrainerBooking::with(['member.user', 'timeSlot'])
                        ->where('trainer_id', $trainer->id)
                        ->latest('session_date')
                        ->get();

                    $trainerMetrics = [
                        'total' => $trainerBookings->count(),
                        'pending' => $trainerBookings->where('status', TrainerBooking::STATUS_PENDING)->count(),
                        'approved' => $trainerBookings->where('status', TrainerBooking::STATUS_APPROVED)->count(),
                        'completed' => $trainerBookings->where('status', TrainerBooking::STATUS_COMPLETED)->count(),
                        'rejected' => $trainerBookings->where('status', TrainerBooking::STATUS_REJECTED)->count(),
                    ];
                }
            }
        }

        return view('welcome', compact(
            'today',
            'activeProducts',
            'activePaymentMethods',
            'operationalSlots',
            'activeTrainers',
            'member',
            'activeMembership',
            'pendingMembership',
            'myUpcomingSchedules',
            'myRecentVisits',
            'myReservations',
            'canMakeReservation',
            'isDailyVisitOnly',
            'trainer',
            'trainerBookings',
            'trainerMetrics'
        ));
    }
}
