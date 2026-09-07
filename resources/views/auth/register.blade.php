@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Register Card -->
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

                        <h4 class="mb-1 text-center">Pendaftaran Akun</h4>
                        <p class="mb-6">Daftar sekarang untuk menjadwalkan kunjungan gym Anda!</p>

                        <form id="formAuthentication" class="mb-6" action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}"
                                    placeholder="Masukkan nama Anda" autocomplete="name" autofocus required />
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Masukkan email Anda" autocomplete="email" required />
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
                                        aria-describedby="password" autocomplete="new-password" required />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
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

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Daftar</button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>Sudah memiliki akun?</span>
                            <a href="{{ route('login') }}">
                                <span>Masuk</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Register Card -->
            </div>
        </div>
    </div>
@endsection
