@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Forgot Password Card -->
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

                        <h4 class="mb-1 text-center">Lupa Kata Sandi?</h4>
                        <p class="mb-6 text-justify">Masukkan email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang
                            kata sandi Anda.</p>

                        {{-- Status / Success Alert --}}
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-check-circle me-2 fs-5"></i>
                                    <div>{{ session('status') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Tutup"></button>
                            </div>
                        @endif

                        <form id="formAuthentication" class="mb-6" action="{{ route('password.email') }}" method="POST">
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

                            <button class="btn btn-primary d-grid w-100" type="submit">Kirim Link Reset Password</button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
                                <i class="icon-base bx bx-chevron-left me-1"></i>
                                <span>Kembali ke Login</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Forgot Password Card -->
            </div>
        </div>
    </div>
@endsection
