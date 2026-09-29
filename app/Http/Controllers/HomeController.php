<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Tampilkan Halaman Utama (Landing Page) Indo Fitness Gym Sport®.
     * Landing page hanya untuk pengunjung publik/tamu sebelum masuk ke sistem.
     * Pengguna yang sudah login akan langsung dialihkan ke dashboard/portal masing-masing.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasAnyRole(['Admin/Manager', 'Kasir'])) {
                return redirect()->route('dashboard');
            }

            // Member biasa maupun Member berstatus Trainer langsung masuk ke halaman Member
            return redirect()->route('member.index');
        }

        $activeProducts = Product::with(['durations' => fn($q) => $q->orderedByDuration()])
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $groupedServices = Product::groupedServices();
        $operationalSlots = TimeSlot::active()->orderBy('start_time')->get();
        $activeTrainers = Member::with('user')->activeTrainers()->get();

        return view('welcome', compact(
            'activeProducts',
            'groupedServices',
            'operationalSlots',
            'activeTrainers'
        ));
    }
}
