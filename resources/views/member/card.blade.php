<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Member - {{ $user->name }} ({{ $member->member_code ?? 'IFGS' }})</title>
    <!-- Fonts & Sneat Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f5fa;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .action-bar {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #696cff;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #5f61e6;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #697a8d;
            border-color: #d9dee3;
        }

        .btn-outline:hover {
            background-color: #f8f9fa;
        }

        /* Member Card Design (CR80 Standard Credit Card Ratio: 85.6mm x 53.98mm) */
        .member-card {
            width: 520px;
            height: 310px;
            background: linear-gradient(135deg, #1e1e38 0%, #2b2c49 50%, #4338ca 100%);
            border-radius: 18px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
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
        }

        .member-card::after {
            content: "";
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(234, 84, 85, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-bottom: 12px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #ffffff;
            text-transform: uppercase;
        }

        .brand-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .badge-membership {
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffab00;
            border: 1px solid rgba(255, 171, 0, 0.4);
        }

        .badge-membership.active {
            color: #71dd37;
            border-color: rgba(113, 221, 55, 0.4);
        }

        .card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            margin-top: 10px;
        }

        .member-info {
            flex: 1;
            padding-right: 20px;
        }

        .member-name {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .member-code {
            font-size: 14px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #696cff;
            background: rgba(255, 255, 255, 0.95);
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 12px;
        }

        .info-row {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 3px;
        }

        .info-row strong {
            color: #ffffff;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
            flex-shrink: 0;
        }

        .qr-wrapper svg {
            display: block;
            width: 110px;
            height: 110px;
        }

        .qr-caption {
            font-size: 9px;
            font-family: monospace;
            color: #1e1e38;
            font-weight: bold;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 10px;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
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
            <div class="member-info">
                <div class="member-name">{{ $user->name }}</div>
                <div class="member-code">{{ $member->member_code ?? $user->slug }}</div>
                <div class="info-row">Paket: <strong>{{ $activeMembership->product->name ?? 'Reguler Member' }}</strong></div>
                @if ($activeMembership)
                    <div class="info-row">Berlaku s/d: <strong>{{ $activeMembership->end_date->format('d M Y') }}</strong></div>
                @endif
                <div class="info-row">No. HP: <strong>{{ $member->phone ?? '-' }}</strong></div>
            </div>

            <div class="qr-wrapper">
                {!! $user->getQrCodeSvg(110) !!}
                <div class="qr-caption">{{ $user->qr_code }}</div>
            </div>
        </div>

        <!-- Footer: Info Absensi -->
        <div class="card-footer">
            <span>Scan kode QR ini di meja resepsionis untuk absensi kunjungan.</span>
            <span>IFGS &bull; Official Digital Card</span>
        </div>
    </div>
</body>
</html>

