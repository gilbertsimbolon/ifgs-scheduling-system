<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

    /**
     * Proses keluar dari akun (logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar dari akun.');
    }

    /**
     * Tampilkan halaman permohonan reset kata sandi (lupa kata sandi).
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi ke email pengguna.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email tidak terdaftar.'])
                ->with('error', 'Email tidak terdaftar.');
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()
                ->with('status', 'Link reset password anda sudah kami kirim ke email ' . $request->email);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)])
            ->with('error', __($status));
    }

    /**
     * Tampilkan formulir atur ulang kata sandi.
     */
    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Proses pengaturan ulang kata sandi pengguna.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'Token reset kata sandi tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
        }

        $errorMessage = match ($status) {
            Password::INVALID_USER => 'Email tidak terdaftar.',
            Password::INVALID_TOKEN => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa.',
            Password::RESET_THROTTLED => 'Terlalu banyak percobaan. Silakan coba lagi beberapa saat.',
            default => 'Gagal mengatur ulang kata sandi. Silakan coba lagi.',
        };

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $errorMessage])
            ->with('error', $errorMessage);
    }
}
