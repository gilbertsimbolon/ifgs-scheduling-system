<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Member - {{ $user->name }} ({{ $member->member_code ?? 'IFGS' }})</title>
    <!-- Fonts & Sneat Icons -->
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo-ifgs.jpg') }}" />
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background-color: #f4f5fa;
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            padding: 24px 16px;
        }

        .action-bar {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            gap: 8px;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #696cff;
            background-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            background-color: #5f61e6;
            background-color: #b91c1c;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #697a8d;
            border-color: #d9dee3;
            color: #334155;
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
            background-color: #f8fafc;
            border-color: #94a3b8;
        }

        /* Member Card Design (CR80 Standard Credit Card Ratio: 85.6mm x 53.98mm) */
        /* Member Card Design - Standard Physical PVC Gym Card Styling */
        .member-card {
            width: 520px;
            height: 310px;
            background: linear-gradient(135deg, #1e1e38 0%, #2b2c49 50%, #4338ca 100%);
            border-radius: 18px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            width: 530px;
            height: 325px;
            background-color: #18191d;
            border: 1px solid #33363f;
            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.22);
            color: #ffffff;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Decorative background shapes */
        .member-card::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(105, 108, 255, 0.25) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        /* Top Header: Nuansa Merah Khas Logo IFGS */
        .card-top-banner {
            background: linear-gradient(135deg, #991b1b 0%, #b91c1c 45%, #dc2626 100%);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #ef4444;
        }

        .member-card::after {
            content: "";
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(234, 84, 85, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            object-fit: cover;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .card-header {
        .brand-text {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 12px;
            flex-direction: column;
        }

        .brand-title {
            font-size: 18px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 1.5px;
            letter-spacing: 0.5px;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.88);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            font-weight: 500;
            margin-top: 2px;
        }

        .badge-membership {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            padding: 4px 12px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffab00;
            border: 1px solid rgba(255, 171, 0, 0.4);
            background-color: rgba(0, 0, 0, 0.35);
            color: #fef08a;
            border: 1px solid rgba(254, 240, 138, 0.45);
        }

        .badge-membership.active {
            color: #71dd37;
            border-color: rgba(113, 221, 55, 0.4);
            background-color: #15803d;
            color: #ffffff;
            border-color: #22c55e;
        }

        .card-body {
        /* Body Section */
        .card-content {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            margin-top: 10px;
            flex: 1;
            background: linear-gradient(180deg, #1c1d22 0%, #16171a 100%);
        }

        .member-info {
            flex: 1;
            padding-right: 20px;
            padding-right: 18px;
        }

        .member-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .member-name {
            font-size: 20px;
            font-weight: 700;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
            line-height: 1.2;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .member-code {
            font-size: 14px;
        .member-code-badge {
            display: inline-block;
            background: #27272a;
            border: 1px solid #3f3f46;
            color: #f87171;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #696cff;
            background: rgba(255, 255, 255, 0.95);
            padding: 3px 8px;
            font-size: 13px;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-table {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-row {
            display: flex;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 3px;
            color: #e2e8f0;
        }

        .info-row strong {
        .info-row .label {
            width: 86px;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .info-row .value {
            font-weight: 600;
            color: #ffffff;
        }

        .qr-wrapper {
        /* QR Section */
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .qr-wrapper svg {
        .qr-section svg {
            display: block;
            width: 110px;
            height: 110px;
            width: 105px;
            height: 105px;
        }

        .qr-caption {
        .qr-code-text {
            font-size: 9px;
            font-family: monospace;
            color: #1e1e38;
            font-weight: bold;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .card-footer {
        /* Footer */
        .card-bottom-bar {
            background-color: #111215;
            border-top: 1px solid #27272a;
            padding: 9px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 10px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 9.5px;
            color: #94a3b8;
        }

        .card-bottom-bar .instructions {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-bottom-bar .instructions i {
            color: #ef4444;
            font-size: 13px;
        }

        .card-bottom-bar .card-type {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #cbd5e1;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
                min-height: auto;
            }

            .action-bar {
                display: none !important;
            }

            .member-card {
                box-shadow: none;
                border: 1px solid #94a3b8;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bx bx-printer"></i> Cetak Kartu Member
        </button>
        <a href="{{ route('user.qr-code.download.user', $user) }}" class="btn btn-outline">
            <i class="bx bx-download"></i> Unduh File QR (SVG)
        </a>
        <button onclick="window.close()" class="btn btn-outline">
            <i class="bx bx-x"></i> Tutup
        </button>
    </div>

    <!-- Digital Member Card Container -->
    <div class="member-card">
        <!-- Header: Logo & Branding -->
        <div class="card-header">
            <div>
                <div class="brand-title">INDO FITNESS</div>
                <div class="brand-sub">Gym Sport &bull; Tondano</div>
        <!-- Header: Logo IFGS & Teks Judul Resmi -->
        <div class="card-top-banner">
            <div class="brand-section">
                <img src="{{ asset('img/logo-ifgs.jpg') }}" alt="IFGS" class="brand-logo" />
                <div class="brand-text">
                    <span class="brand-title">Indo Fitness Gym Sport®</span>
                    <span class="brand-sub">Tondano &bull; Kartu Member Resmi</span>
                </div>
            </div>
            <div>
                @if ($activeMembership)
                    <span class="badge-membership active">Member Aktif</span>
                @else
                    <span class="badge-membership">Member Gym</span>
                @endif
            </div>
        </div>

        <!-- Body: Member details & QR Absensi -->
        <div class="card-body">
        <div class="card-content">
            <div class="member-info">
                <div class="member-label">Nama Anggota</div>
                <div class="member-name">{{ $user->name }}</div>
                <div class="member-code">{{ $member->member_code ?? $user->slug }}</div>
                <div class="info-row">Paket: <strong>{{ $activeMembership->product->name ?? 'Reguler Member' }}</strong></div>
                @if ($activeMembership)
                    <div class="info-row">Berlaku s/d: <strong>{{ $activeMembership->end_date->format('d M Y') }}</strong></div>
                @endif
                <div class="info-row">No. HP: <strong>{{ $member->phone ?? '-' }}</strong></div>
                <div class="member-code-badge">{{ $member->member_code ?? ($user->user_code ?? $user->slug) }}</div>
                
                <div class="info-table">
                    <div class="info-row">
                        <span class="label">Paket</span>
                        <span class="value">: {{ $activeMembership->product->name ?? 'Reguler Member' }}</span>
                    </div>
                    @if ($activeMembership)
                        <div class="info-row">
                            <span class="label">Berlaku s/d</span>
                            <span class="value">: {{ $activeMembership->end_date->format('d M Y') }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="label">No. HP</span>
                        <span class="value">: {{ $member->phone ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="qr-wrapper">
                {!! $user->getQrCodeSvg(110) !!}
                <div class="qr-caption">{{ $user->qr_code }}</div>
            <div class="qr-section">
                {!! $user->getQrCodeSvg(105) !!}
                <div class="qr-code-text">{{ $user->qr_code }}</div>
            </div>
        </div>

        <!-- Footer: Info Absensi -->
        <div class="card-footer">
            <span>Scan kode QR ini di meja resepsionis untuk absensi kunjungan.</span>
            <span>IFGS &bull; Official Digital Card</span>
        <div class="card-bottom-bar">
            <div class="instructions">
                <i class="bx bx-qr-scan"></i>
                <span>Scan QR saat kedatangan di meja resepsionis gym</span>
            </div>
            <span class="card-type">IFGS GYM SPORT</span>
        </div>
    </div>
</body>
</html>

