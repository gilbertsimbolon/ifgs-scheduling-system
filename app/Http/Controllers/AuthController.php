<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
}
