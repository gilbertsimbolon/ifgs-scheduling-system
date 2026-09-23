<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $featuredProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->orderBy('price')
            ->take(3)
            ->get();

        // Presensi Hari Ini jika ada
        $currentAttendance = $member?->currentAttendanceToday();

        // Slot Operasional untuk modal reservasi cepat
        $operationalSlots = TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)
            ->orderBy('start_time')
            ->get();

        return view('member-portal.index', compact(
            'user',
            'member',
            'activeMembership',
            'pendingMembership',
            'upcomingSchedule',
            'upcomingReservation',
            'featuredProducts',
            'currentAttendance',
            'operationalSlots'
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

        $operationalSlots = TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)
            ->orderBy('start_time')
            ->get();

        $activeReservations = $member ? $member->reservations()
            ->with(['timeSlot', 'membership.product', 'schedule'])
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_SCHEDULED])
            ->orderByDesc('visit_date')
            ->get() : collect();

        $upcomingSchedules = $member ? $member->schedules()
            ->with('timeSlot')
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', [Schedule::STATUS_SCHEDULED, 'scheduled'])
            ->orderBy('scheduled_date')
            ->get() : collect();

        return view('member-portal.reservasi', compact(
            'user',
            'member',
            'activeMembership',
            'isDailyVisit',
            'canMakeReservation',
            'operationalSlots',
            'activeReservations',
            'upcomingSchedules'
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

        $attendances = $member ? $member->attendances()
            ->orderByDesc('date')
            ->orderByDesc('check_in_at')
            ->paginate(15) : collect();

        $pastSchedules = $member ? $member->schedules()
            ->with('timeSlot')
            ->whereIn('status', [Schedule::STATUS_ATTENDED, Schedule::STATUS_NO_SHOW, Schedule::STATUS_CANCELLED])
            ->orWhere(function ($q) {
                $q->whereDate('scheduled_date', '<', now()->toDateString());
            })
            ->orderByDesc('scheduled_date')
            ->take(15)
            ->get() : collect();

        return view('member-portal.riwayat', compact(
            'user',
            'member',
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

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->orderBy('price')
            ->get();

        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)
            ->get();

        $activeMembership = $member?->activeMembership();
        $pendingMembership = $member ? $member->memberships()
            ->with(['product', 'paymentMethod'])
            ->where('status', Membership::STATUS_PENDING)
            ->latest()
            ->first() : null;

        $myMemberships = $member ? $member->memberships()
            ->with(['product', 'paymentMethod', 'transaction'])
            ->latest()
            ->get() : collect();

        return view('member-portal.paket-layanan', compact(
            'user',
            'member',
            'products',
            'paymentMethods',
            'activeMembership',
            'pendingMembership',
            'myMemberships'
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
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated, $user) {
            if ($validated['name'] !== $user->name) {
                $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if ($request->hasFile('avatar')) {
                if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
                }
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
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
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->route('member.profil')->with('success', 'Password Anda berhasil diperbarui.');
    }
}

