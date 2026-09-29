<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\User;
use App\Services\GreedySchedulingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    /**
     * Memastikan user adalah Member. Jika Admin/Kasir mengakses, arahkan ke dashboard backoffice.
     */
    protected function ensureMember(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasRole('Member')) {
            if ($user->hasAnyRole(['Admin/Manager', 'Kasir'])) {
                return redirect()->route('dashboard');
            }
            abort(403, 'Akses terbatas untuk akun Member.');
        }

        return null;
    }

    /**
     * 1. Member Home (/member)
     * Menampilkan status membership, reservasi terdekat, paket layanan rekomendasi, dan aksi cepat.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user()->load(['roles', 'member']);
        $member = $user->member;

        // Status Membership
        $activeMembership = $member?->activeMembership();
        $pendingMembership = $member ? $member->memberships()
            ->with(['product', 'paymentMethod'])
            ->where('status', Membership::STATUS_PENDING)
            ->latest()
            ->first() : null;

        // Reservasi Terdekat
        $upcomingSchedule = null;
        $upcomingReservation = null;

        if ($member) {
            $upcomingSchedule = $member->schedules()
                ->with('timeSlot')
                ->whereDate('scheduled_date', '>=', now()->toDateString())
                ->whereIn('status', [Schedule::STATUS_SCHEDULED, 'scheduled'])
                ->orderBy('scheduled_date')
                ->first();

            if (! $upcomingSchedule) {
                $upcomingReservation = $member->reservations()
                    ->with('timeSlot')
                    ->whereDate('visit_date', '>=', now()->toDateString())
                    ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_SCHEDULED])
                    ->orderBy('visit_date')
                    ->first();
            }
        }

        // Paket Layanan Tersedia (dari database riil)
        $featuredProducts = Product::with(['durations' => fn ($q) => $q->orderBy('duration_value')])
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->take(3)
            ->get();

        // Presensi Hari Ini jika ada
        $currentAttendance = $member?->currentAttendanceToday();

        // Slot Operasional untuk modal reservasi cepat & kapasitas gym
        $operationalSlots = TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)
            ->orderBy('start_time')
            ->get();

        // Kondisi Operasional & Status Keramaian Gym Real-time Hari Ini
        $now = Carbon::now();
        $isSunday = $now->isSunday();
        $openTime = Carbon::createFromTime(8, 0, 0);
        $closeTime = Carbon::createFromTime(22, 0, 0);
        $isOpenNow = ! $isSunday && $now->between($openTime, $closeTime);

        $gymStatus = [
            'is_open' => $isOpenNow,
            'is_sunday' => $isSunday,
            'operating_hours' => '08:00 - 22:00 WITA',
            'status_label' => $isSunday ? 'Tutup (Libur Hari Minggu)' : ($isOpenNow ? 'Buka Sekarang' : 'Tutup (Buka 08:00 WITA)'),
            'status_badge_class' => $isSunday ? 'bg-secondary' : ($isOpenNow ? 'bg-success' : 'bg-dark'),
        ];

        // Metrik kehadiran & keramaian realtime hari ini
        $today = Carbon::today()->format('Y-m-d');
        $inGymCount = Attendance::today()->currentlyInGym()->count();
        $todayCheckinCount = Attendance::today()->count();
        $todayCheckoutCount = Attendance::today()->whereNotNull('check_out_at')->count();
        $activeTrainerSessionsCount = 0;

        $totalGymCapacity = $operationalSlots->sum('capacity') ?: 100;
        $crowdPercentage = $totalGymCapacity > 0 ? min(100, (int) round(($inGymCount / $totalGymCapacity) * 100)) : 0;

        if ($isSunday || ! $isOpenNow) {
            $crowdLevel = [
                'level' => 'closed',
                'label' => 'Gym Sedang Tutup',
                'badge_class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
                'description' => $isSunday ? 'IFGS Gym libur operasional setiap hari Minggu.' : 'Gym saat ini di luar jam operasional (Buka 08:00 - 22:00 WITA).',
                'progress_class' => 'bg-secondary',
                'icon' => 'bx-moon',
            ];
        } elseif ($crowdPercentage <= 35) {
            $crowdLevel = [
                'level' => 'sepi',
                'label' => 'Sepi / Lengang',
                'badge_class' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                'description' => 'Banyak alat latihan tersedia, suasana sangat leluasa untuk berolahraga.',
                'progress_class' => 'bg-success',
                'icon' => 'bx-smile',
            ];
        } elseif ($crowdPercentage <= 70) {
            $crowdLevel = [
                'level' => 'sedang',
                'label' => 'Kondisi Normal / Sedang',
                'badge_class' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                'description' => 'Suasana latihan kondusif dengan jumlah pengunjung yang stabil.',
                'progress_class' => 'bg-warning',
                'icon' => 'bx-user-check',
            ];
        } else {
            $crowdLevel = [
                'level' => 'ramai',
                'label' => 'Ramai / Padat',
                'badge_class' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                'description' => 'Area latihan cukup padat, disarankan saling bergantian menggunakan alat.',
                'progress_class' => 'bg-danger',
                'icon' => 'bx-group',
            ];
        }

        $crowdMetrics = [
            'in_gym_count' => $inGymCount,
            'today_checkin_count' => $todayCheckinCount,
            'today_checkout_count' => $todayCheckoutCount,
            'active_trainer_sessions' => $activeTrainerSessionsCount,
            'total_capacity' => $totalGymCapacity,
            'percentage' => $crowdPercentage,
            'crowd_level' => $crowdLevel,
        ];

        // Detail okupansi per sesi layanan aktif
        $slotOccupancies = $operationalSlots->map(function ($slot) use ($today) {
            $schedulesCount = Schedule::whereDate('scheduled_date', $today)
                ->where('time_slot_id', $slot->id)
                ->whereIn('status', [Schedule::STATUS_SCHEDULED, Schedule::STATUS_ATTENDED])
                ->count();

            return [
                'id' => $slot->id,
                'name' => $slot->name,
                'category' => $slot->category,
                'time_range' => $slot->time_range,
                'capacity' => $slot->capacity,
                'reservation_quota' => $slot->effective_reservation_quota,
                'schedules_count' => $schedulesCount,
            ];
        });

        // Trainer booking yang aktif/disetujui untuk sesi jadwal mendatang (trainer hanya sumber informasi)
        $upcomingTrainerBooking = null;

        return view('member-portal.index', compact(
            'user',
            'member',
            'activeMembership',
            'pendingMembership',
            'upcomingSchedule',
            'upcomingReservation',
            'upcomingTrainerBooking',
            'featuredProducts',
            'currentAttendance',
            'operationalSlots',
            'gymStatus',
            'crowdMetrics',
            'slotOccupancies'
        ));
    }

    /**
     * 2. Reservasi Member (/member/reservasi)
     * Menampilkan formulir reservasi baru dan daftar reservasi aktif member.
     */
    public function reservasi(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user()->load(['member']);
        $member = $user->member;

        $activeMembership = $member?->activeMembership();
        $isDailyVisit = $member?->hasOnlyDailyVisitMembership();
        $canMakeReservation = $member?->canMakeReservation() ?? false;

        $activeMemberships = $member ? $member->memberships()
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->with('product')
            ->get() : collect();

        $subscribedCategories = [];
        foreach ($activeMemberships as $ms) {
            if ($ms->product) {
                $subscribedCategories = array_merge($subscribedCategories, $ms->product->supportedCategories());
            }
        }
        $subscribedCategories = array_values(array_unique($subscribedCategories));

        $trainers = Trainer::with('user')->active()->get();

        $operationalSlots = TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)
            ->orderBy('start_time')
            ->get();

        $greedyService = app(GreedySchedulingService::class);

        $defaultSelectedSlot = $operationalSlots->first(fn ($s) => in_array($s->category, $subscribedCategories)) ?? $operationalSlots->first();
        $defaultSelectedSlotId = $defaultSelectedSlot?->id;

        $slotDatesMap = [];
        foreach ($operationalSlots as $slot) {
            $upcomingDates = $slot->getUpcomingOperationalDates(10);
            $slotDatesMap[$slot->id] = [];

            foreach ($upcomingDates as $d) {
                $dateStr = $d->toDateString();
                $occ = $greedyService->calculateOccupanciesForDate($dateStr, collect([$slot]));
                $occupied = $occ[$slot->id] ?? 0;
                $quota = $slot->effective_reservation_quota;
                $remaining = max(0, $quota - $occupied);

                $slotDatesMap[$slot->id][] = [
                    'date' => $dateStr,
                    'day_name' => $d->translatedFormat('l'),
                    'day_short' => $d->translatedFormat('D'),
                    'day_number' => $d->format('d'),
                    'month_name' => $d->translatedFormat('M'),
                    'is_today' => $d->isToday(),
                    'label' => $d->isToday() ? 'Hari Ini' : ($d->isTomorrow() ? 'Besok' : $d->translatedFormat('D')),
                    'quota' => $quota,
                    'occupied' => $occupied,
                    'remaining' => $remaining,
                    'formatted' => "{$remaining}/{$quota}",
                ];
            }
        }

        $availableDays = $slotDatesMap[$defaultSelectedSlotId] ?? ($slotDatesMap[$operationalSlots->first()?->id] ?? []);

        $activeReservations = $member ? $member->reservations()
            ->with(['timeSlot', 'membership.product', 'schedule'])
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_SCHEDULED])
            ->orderByDesc('visit_date')
            ->get() : collect();

        $upcomingSchedules = $member ? $member->schedules()
            ->with(['timeSlot', 'reservation'])
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', [Schedule::STATUS_SCHEDULED, 'scheduled'])
            ->orderBy('scheduled_date')
            ->get() : collect();

        $trainerBookings = collect();

        return view('member-portal.reservasi', compact(
            'user',
            'member',
            'activeMembership',
            'activeMemberships',
            'subscribedCategories',
            'trainers',
            'availableDays',
            'slotDatesMap',
            'defaultSelectedSlotId',
            'isDailyVisit',
            'canMakeReservation',
            'operationalSlots',
            'activeReservations',
            'upcomingSchedules',
            'trainerBookings'
        ));
    }

    /**
     * 3. Riwayat Kunjungan & Absensi (/member/riwayat)
     * Menampilkan riwayat presensi check-in/out dan riwayat reservasi masa lalu.
     */
    public function riwayat(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user()->load(['member']);
        $member = $user->member;

        $myMemberships = $member ? $member->memberships()
            ->with(['product', 'paymentMethod', 'transaction'])
            ->latest()
            ->get() : collect();

        $attendances = $member ? $member->attendances()
            ->orderByDesc('date')
            ->orderByDesc('check_in_at')
            ->take(20)
            ->get() : collect();

        $pastSchedules = $member ? $member->schedules()
            ->with('timeSlot')
            ->where(function ($q) {
                $q->whereIn('status', [Schedule::STATUS_ATTENDED, Schedule::STATUS_NO_SHOW, Schedule::STATUS_CANCELLED])
                    ->orWhereDate('scheduled_date', '<', now()->toDateString());
            })
            ->orderByDesc('scheduled_date')
            ->take(20)
            ->get() : collect();

        $activityLogs = collect();

        foreach ($attendances as $att) {
            $isCurrentlyInGym = ($att->status === Attendance::STATUS_CHECKED_IN && is_null($att->check_out_at));
            $attDate = $att->date ? Carbon::parse($att->date) : ($att->check_in_at ?? now());

            $activityLogs->push([
                'type' => 'attendance',
                'title' => 'Kehadiran Gym',
                'date' => $attDate,
                'check_in_at' => $att->check_in_at ? $att->check_in_at->format('H:i') : '-',
                'check_out_at' => $att->check_out_at ? $att->check_out_at->format('H:i') : null,
                'status_label' => $isCurrentlyInGym ? 'Sedang di Gym' : 'Selesai',
                'status_badge_class' => $isCurrentlyInGym
                    ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'
                    : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
                'notes' => $att->notes,
                'icon' => 'bx-check-circle',
                'icon_bg' => 'rgba(25, 135, 84, 0.1)',
                'icon_color' => 'text-success',
                'timestamp' => $att->check_in_at ? $att->check_in_at->timestamp : $attDate->timestamp,
            ]);
        }

        foreach ($pastSchedules as $sched) {
            $statusLabel = 'Selesai';
            $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25';
            if ($sched->status === Schedule::STATUS_ATTENDED) {
                $statusLabel = 'Hadir';
                $badgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
            } elseif ($sched->status === Schedule::STATUS_NO_SHOW) {
                $statusLabel = 'Tidak Hadir';
                $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
            } elseif ($sched->status === Schedule::STATUS_CANCELLED) {
                $statusLabel = 'Dibatalkan';
                $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25';
            }

            $schedDate = $sched->scheduled_date ? Carbon::parse($sched->scheduled_date) : now();

            $activityLogs->push([
                'type' => 'schedule',
                'title' => 'Jadwal Latihan',
                'date' => $schedDate,
                'time_slot' => $sched->timeSlot?->formatted_time ?? 'Waktu disesuaikan',
                'status_label' => $statusLabel,
                'status_badge_class' => $badgeClass,
                'notes' => $sched->notes,
                'icon' => 'bx-calendar-event',
                'icon_bg' => 'rgba(13, 110, 253, 0.1)',
                'icon_color' => 'text-primary',
                'timestamp' => $schedDate->timestamp,
            ]);
        }

        $activityLogs = $activityLogs->sortByDesc('timestamp')->values();

        return view('member-portal.riwayat', compact(
            'user',
            'member',
            'myMemberships',
            'activityLogs',
            'attendances',
            'pastSchedules'
        ));
    }

    /**
     * 4. Paket Layanan (/member/paket-layanan)
     * Menampilkan seluruh paket membership dari database dan riwayat langganan member.
     */
    public function paketLayanan(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user()->load(['member']);
        $member = $user->member;

        $products = Product::with(['durations' => fn ($q) => $q->orderBy('duration_value')])
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)
            ->where('type', '!=', PaymentMethod::TYPE_CASH)
            ->orderBy('name')
            ->get();

        $activeMembership = $member?->activeMembership();
        $pendingMembership = $member ? $member->memberships()
            ->with(['product', 'paymentMethod'])
            ->where('status', Membership::STATUS_PENDING)
            ->latest()
            ->first() : null;

        return view('member-portal.paket-layanan', compact(
            'user',
            'member',
            'products',
            'paymentMethods',
            'activeMembership',
            'pendingMembership'
        ));
    }

    /**
     * 5. Profil Member (/member/profil)
     * Menampilkan data diri, QR code absensi digital, form edit profil, dan form ubah sandi.
     */
    public function profil(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user()->load(['roles', 'member.memberships.product']);
        $member = $user->member;
        $activeMembership = $member?->activeMembership();

        return view('member-portal.profil', compact(
            'user',
            'member',
            'activeMembership'
        ));
    }

    /**
     * Perbarui data diri member (nama, email, phone, foto profil).
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan pada akun lain.',
            'phone.max' => 'Nomor handphone maksimal 20 karakter.',
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.max' => 'Ukuran file foto profil maksimal 2MB.',
        ]);

        DB::transaction(function () use ($request, $validated, $user) {
            if ($validated['name'] !== $user->name) {
                $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            } elseif ($request->boolean('remove_avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = null;
            }

            $user->save();

            if ($user->member) {
                $user->member->update(['phone' => $validated['phone'] ?? null]);
            } else {
                Member::create([
                    'user_id' => $user->id,
                    'phone' => $validated['phone'] ?? null,
                ]);
            }
        });

        return redirect()->route('member.profil')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Upload / perbarui foto profil member secara langsung.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Silakan pilih file foto terlebih dahulu.',
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto profil harus jpeg, png, jpg, atau webp.',
            'avatar.max' => 'Ukuran file foto profil maksimal 2MB.',
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return redirect()->route('member.profil')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus foto profil member dan kembalikan ke inisial nama.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return redirect()->route('member.profil')->with('success', 'Foto profil berhasil dihapus.');
    }

    /**
     * Perbarui kata sandi member.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureMember($request)) {
            return $redirect;
        }

        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('member.profil')->with('success', 'Password Anda berhasil diperbarui.');
    }
}
