<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::validate($credentials)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Email atau kata sandi yang Anda masukkan salah.');
        }

        /** @var User $user */
        $user = Auth::getLastAttempted();

        if ($user->status !== User::STATUS_ACTIVE) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi pengelola.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $defaultDestination = $user->hasRole('Member')
            ? url('/')
            : route('pengguna.index');

        return redirect()->intended($defaultDestination);
    }

    /**
     * Tampilkan halaman registrasi.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran akun pengguna baru.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => User::STATUS_ACTIVE,
        ]);

        Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
        $user->assignRole('Member');

        return redirect()
            ->route('login')
            ->with('success', 'Registrasi berhasil! Silakan masuk dengan akun Anda.');
    }
}
