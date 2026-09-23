<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Member - {{ $user->name }} ({{ $member->member_code ?? 'IFGS' }})</title>
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo-ifgs.jpg') }}" />
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/boxicons.css') }}" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
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
            gap: 8px;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #dc2626;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            background-color: #b91c1c;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-outline:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }

        /* Member Card Design - Standard Physical PVC Gym Card Styling */
        .member-card {
            width: 530px;
            height: 325px;
            background-color: #18191d;
            border: 1px solid #33363f;
            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.22);
            color: #ffffff;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Top Header: Nuansa Merah Khas Logo IFGS */
        .card-top-banner {
            background: linear-gradient(135deg, #991b1b 0%, #b91c1c 45%, #dc2626 100%);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #ef4444;
        }

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

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.88);
            letter-spacing: 0.8px;
            text-transform: uppercase;
            font-weight: 500;
            margin-top: 2px;
        }

        .badge-membership {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background-color: rgba(0, 0, 0, 0.35);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* Body Section */
        .card-content {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1;
            background: linear-gradient(180deg, #1c1d22 0%, #16171a 100%);
        }

        .member-info {
            flex: 1;
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
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
            line-height: 1.2;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .member-code-badge {
            display: inline-block;
            background: #27272a;
            border: 1px solid #3f3f46;
            color: #f87171;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            font-size: 13px;
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .info-table {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-row {
            display: flex;
            font-size: 11px;
            color: #e2e8f0;
            line-height: 1.4;
        }

        .info-row .label {
            width: 76px;
            color: #94a3b8;
            flex-shrink: 0;
        }

        .info-row .value {
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 195px;
        }

        /* QR Section */
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .qr-section svg {
            display: block;
            width: 105px;
            height: 105px;
        }

        .qr-code-text {
            font-size: 9px;
            font-family: monospace;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .card-bottom-bar {
            background-color: #111215;
            border-top: 1px solid #27272a;
            padding: 9px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                <span class="badge-membership">MEMBER RESMI</span>
            </div>
        </div>

        <!-- Body: Member details & QR Absensi -->
        <div class="card-content">
            <div class="member-info">
                <div class="member-label">Nama Anggota</div>
                <div class="member-name">{{ $user->name }}</div>
                <div class="member-code-badge">{{ $member->member_code ?? ($user->user_code ?? $user->slug) }}</div>

                <div class="info-table">
                    <div class="info-row">
                        <span class="label">No. HP</span>
                        <span class="value">: {{ $member->phone ?? ($user->phone ?? '-') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">: {{ $user->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Bergabung</span>
                        <span class="value">: {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="qr-section">
                {!! $user->getQrCodeSvg(105) !!}
                <div class="qr-code-text">{{ $user->qr_code }}</div>
            </div>
        </div>

        <!-- Footer: Info Absensi -->
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
