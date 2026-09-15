<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna lengkap dengan QR Code, data diri, dan form ganti password.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load(['roles', 'member.memberships.product', 'trainer']);
        $isMember = $user->hasRole('Member');
        $activeMembership = $user->member ? $user->member->activeMembership() : null;
        $roleName = $user->roles->first()?->name ?? 'Pengguna';

        return view('profile.index', [
            'user' => $user,
            'isMember' => $isMember,
            'activeMembership' => $activeMembership,
            'roleName' => $roleName,
        ]);
    }

    /**
     * Perbarui informasi data diri pengguna (nama, email, nomor handphone).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 255 karakter.',
            'email.unique' => 'Alamat email sudah terdaftar pada akun lain.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            if ($validated['name'] !== $user->name) {
                $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
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
