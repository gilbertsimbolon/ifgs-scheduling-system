@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Login Card -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
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

                        <h4 class="mb-1 text-center">Selamat Datang</h4>
                        <p class="mb-6">Silakan masuk ke akun Anda untuk melanjutkan!</p>

                        {{-- Alert Success / Registration Success --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-check-circle me-2 fs-5"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Tutup"></button>
                            </div>
                        @endif

                        {{-- Alert Authentication / Inactive Error --}}
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

                        <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Masukkan email Anda" autocomplete="email" autofocus required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password">Kata Sandi</label>
                                <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" autocomplete="current-password" required />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-8">
                                <div class="d-flex justify-content-between mt-n2">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                            value="1" {{ old('remember') ? 'checked' : '' }} />
                                        <label class="form-check-label" for="remember-me"> Ingat Saya </label>
                                    </div>
                                    <a href="{{ route('password.request') }}">
                                        <span>Lupa Kata Sandi?</span>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>Belum memiliki akun?</span>
                            <a href="{{ route('register') }}">
                                <span>Daftar</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Login Card -->
            </div>
        </div>
    </div>
@endsection
