@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Reset Password Card -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo: Vertikal & Terpusat -->
                        <div class="app-brand justify-content-center text-center mb-4">
                            <a href="{{ url('/') }}"
                                class="app-brand-link d-flex flex-column align-items-center text-center text-decoration-none">
                                <span class="app-brand-logo mb-2 d-flex justify-content-center">
                                    <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="Indo Fitness Gym Sport®"
                                        class="rounded-circle shadow-sm"
                                        style="width: 72px; height: 72px; object-fit: cover;" />
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold text-center"
                                    style="font-size: 1.25rem; line-height: 1.3;">Indo Fitness Gym Sport®</span>
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 text-center">Atur Ulang Kata Sandi</h4>
                        <p class="mb-6 text-center">Masukkan kata sandi baru untuk akun Anda.</p>

                        {{-- Alert Error --}}
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle me-2 fs-5"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Tutup"></button>
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-6" action="{{ route('password.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $email) }}"
                                    placeholder="Masukkan email Anda" autocomplete="email" required readonly />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password">Kata Sandi Baru</label>
                                <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" autocomplete="new-password" autofocus required />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                <div
                                    class="input-group input-group-merge @error('password_confirmation') is-invalid @enderror">
                                    <input type="password" id="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        name="password_confirmation"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password_confirmation" autocomplete="new-password" required />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button class="btn btn-primary d-grid w-100" type="submit">Atur Ulang Kata Sandi</button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="icon-base bx bx-chevron-left me-1"></i>
                                <span>Kembali ke Login</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Reset Password Card -->
            </div>
        </div>
    </div>
@endsection

