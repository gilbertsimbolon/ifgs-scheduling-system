<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - Indo Fitness Gym Sport®</title>
    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f5f5f9;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f5f5f9;
            padding: 40px 15px;
        }
        .email-content {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
            border: 1px solid #eceef1;
        }
        .email-header {
            padding: 35px 30px 20px;
            text-align: center;
            background-color: #ffffff;
        }
        .brand-logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: #566a7f;
            margin-top: 12px;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }
        .brand-subtitle {
            font-size: 12px;
            color: #a1acb8;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .email-divider {
            height: 1px;
            background-color: #f0f1f3;
            margin: 0 30px;
        }
        .email-body {
            padding: 30px 35px;
            color: #697a8d;
            font-size: 15px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #566a7f;
            margin-bottom: 15px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: #696cff;
            color: #ffffff !important;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 3px 10px rgba(105, 108, 255, 0.35);
        }
        .note-box {
            background-color: #f8f9fa;
            border-left: 4px solid #696cff;
            padding: 14px 16px;
            border-radius: 4px;
            margin: 24px 0 16px;
            font-size: 13px;
            color: #697a8d;
            line-height: 1.5;
        }
        .fallback-link {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed #e7e7e8;
            font-size: 12px;
            color: #a1acb8;
            line-height: 1.5;
            word-break: break-all;
        }
        .fallback-link a {
            color: #696cff;
            text-decoration: underline;
        }
        .email-footer {
            text-align: center;
            padding: 24px 20px;
            font-size: 12px;
            color: #a1acb8;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-content">
            <!-- Header Brand: Logo on Top, Name Below, Centered -->
            <div class="email-header">
                @if (isset($message) && file_exists(public_path('img/logo-ifgs.jpg')))
                    <img src="{{ $message->embed(public_path('img/logo-ifgs.jpg')) }}" alt="Indo Fitness Gym Sport®" class="brand-logo" width="72" height="72" />
                @else
                    <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="Indo Fitness Gym Sport®" class="brand-logo" width="72" height="72" />
                @endif
                <h1 class="brand-title">Indo Fitness Gym Sport®</h1>
                <p class="brand-subtitle">Sistem Informasi Penjadwalan Kunjungan Member</p>
            </div>

            <div class="email-divider"></div>

            <!-- Email Body -->
            <div class="email-body">
                <div class="greeting">Halo, {{ $user->name }}!</div>

                <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda di <strong>Indo Fitness Gym Sport®</strong>.</p>
                <p>Silakan klik tombol di bawah ini untuk melanjutkan pembuatan kata sandi baru akun Anda:</p>

                <!-- Action Button -->
                <div class="btn-container">
                    <a href="{{ $resetUrl }}" target="_blank" class="btn-primary">
                        Atur Ulang Kata Sandi
                    </a>
                </div>

                <!-- Expiration & Security Info -->
                <div class="note-box">
                    <strong>Informasi Keamanan:</strong><br>
                    • Tautan reset kata sandi ini berlaku selama <strong>{{ $count }} menit</strong>.<br>
                    • Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini dan kata sandi Anda tetap aman.<br>
                    • Jangan bagikan email atau tautan ini kepada siapa pun.
                </div>

                <!-- Fallback Link -->
                <div class="fallback-link">
                    Jika Anda mengalami kendala saat mengklik tombol di atas, salin dan tempel tautan berikut ke peramban (browser) Anda:<br>
                    <a href="{{ $resetUrl }}" target="_blank">{{ $resetUrl }}</a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            &copy; {{ date('Y') }} Indo Fitness Gym Sport®. Seluruh hak cipta dilindungi.<br>
            Tondano, Minahasa, Sulawesi Utara.
        </div>
    </div>
</body>
</html>
