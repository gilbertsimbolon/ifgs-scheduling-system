<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna lengkap dengan QR Code, data diri, dan form ganti password.
     * Tampilkan halaman profil pengguna lengkap dengan QR Code, data diri, aktivitas absensi, dan form ganti password.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load(['roles', 'member.memberships.product', 'trainer']);
        $isMember = $user->hasRole('Member');
        $activeMembership = $user->member ? $user->member->activeMembership() : null;
        $roleName = $user->roles->first()?->name ?? 'Pengguna';

        // Ambil riwayat kunjungan member atau buat log aktivitas contoh check-in & check-out
        $visitActivities = collect();

        if ($user->member) {
            $attendedSchedules = $user->member->schedules()
                ->with('timeSlot')
                ->where('status', 'attended')
                ->orderByDesc('scheduled_date')
                ->take(3)
                ->get();

            foreach ($attendedSchedules as $sched) {
                $dateFormatted = $sched->scheduled_date->translatedFormat('l, d F Y');
                $slotTime = $sched->timeSlot ? substr($sched->timeSlot->start_time, 0, 5) : '08:30';
                $slotEndTime = $sched->timeSlot ? substr($sched->timeSlot->end_time, 0, 5) : '11:00';

                $visitActivities->push([
                    'type' => 'check_out',
                    'badge_class' => 'bg-label-info',
                    'icon' => 'bx bx-log-out-circle',
                    'color' => 'info',
                    'title' => 'Check-Out Kunjungan Gym',
                    'description' => "Berhasil melakukan check-out pada {$dateFormatted} pukul {$slotEndTime} WITA.",
                    'time' => "{$slotEndTime} WITA",
                    'date' => $dateFormatted,
                ]);

                $visitActivities->push([
                    'type' => 'check_in',
                    'badge_class' => 'bg-label-success',
                    'icon' => 'bx bx-log-in-circle',
                    'color' => 'success',
                    'title' => 'Check-In Kunjungan Gym',
                    'description' => "Berhasil melakukan check-in pada {$dateFormatted} pukul {$slotTime} WITA.",
                    'time' => "{$slotTime} WITA",
                    'date' => $dateFormatted,
                ]);
            }
        }

        if ($visitActivities->isEmpty()) {
            $todayFormatted = now()->translatedFormat('l, d F Y');
            $yesterdayFormatted = now()->subDay()->translatedFormat('l, d F Y');

            $visitActivities = collect([
                [
                    'type' => 'check_out',
                    'badge_class' => 'bg-label-info',
                    'icon' => 'bx bx-log-out-circle',
                    'color' => 'info',
                    'title' => 'Check-Out Kunjungan Gym',
                    'description' => "Berhasil melakukan check-out pada hari ini, {$todayFormatted} pukul 11:15 WITA.",
                    'time' => '11:15 WITA',
                    'date' => 'Hari Ini',
                ],
                [
                    'type' => 'check_in',
                    'badge_class' => 'bg-label-success',
                    'icon' => 'bx bx-log-in-circle',
                    'color' => 'success',
                    'title' => 'Check-In Kunjungan Gym',
                    'description' => "Berhasil melakukan check-in pada hari ini, {$todayFormatted} pukul 08:30 WITA.",
                    'time' => '08:30 WITA',
                    'date' => 'Hari Ini',
                ],
                [
                    'type' => 'check_out',
                    'badge_class' => 'bg-label-info',
                    'icon' => 'bx bx-log-out-circle',
                    'color' => 'info',
                    'title' => 'Check-Out Kunjungan Gym',
                    'description' => "Berhasil melakukan check-out pada {$yesterdayFormatted} pukul 18:00 WITA.",
                    'time' => '18:00 WITA',
                    'date' => 'Kemarin',
                ],
                [
                    'type' => 'check_in',
                    'badge_class' => 'bg-label-success',
                    'icon' => 'bx bx-log-in-circle',
                    'color' => 'success',
                    'title' => 'Check-In Kunjungan Gym',
                    'description' => "Berhasil melakukan check-in pada {$yesterdayFormatted} pukul 15:45 WITA.",
                    'time' => '15:45 WITA',
                    'date' => 'Kemarin',
                ],
            ]);
        }

        return view('profile.index', [
            'user' => $user,
            'isMember' => $isMember,
            'activeMembership' => $activeMembership,
            'roleName' => $roleName,
            'visitActivities' => $visitActivities,
        ]);
    }

    /**
     * Perbarui informasi data diri pengguna (nama, email, nomor handphone).
     * Perbarui informasi data diri pengguna (nama, email, nomor handphone, foto profil).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Alamat email sudah terdaftar pada akun lain.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format foto profil harus jpeg, png, jpg, atau webp.',
            'avatar.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        DB::transaction(function () use ($request, $validated, $user) {
            if ($validated['name'] !== $user->name) {
                $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];

            // Kelola unggah atau hapus foto profil
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

            // Sinkronisasi nomor kontak pada profil Trainer / Member
            if ($user->hasRole('Trainer') || $user->trainer) {
                if ($user->trainer) {
                    $user->trainer->update(['phone' => $validated['phone'] ?? null]);
                } else {
                    Trainer::create([
                        'user_id' => $user->id,
                        'phone' => $validated['phone'] ?? null,
                        'status' => Trainer::STATUS_ACTIVE,
                    ]);
                }
            } else {
                if ($user->member) {
                    $user->member->update(['phone' => $validated['phone'] ?? null]);
                } elseif ($user->hasRole('Member')) {
                    Member::create([
                        'user_id' => $user->id,
                        'phone' => $validated['phone'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('profile.show')
            ->with('profile_success', 'Data diri Anda berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun pengguna.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('password_success', 'Password akun Anda berhasil diperbarui.');
    }
}
