<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 15px;
            width: 320px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .border-top {
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .my-2 {
            margin-top: 8px;
            margin-bottom: 8px;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
        }

        .btn-print {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            margin-bottom: 15px;
            width: 100%;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Cetak Struk (Print)</button>
    </div>

    <div class="text-center mb-2">
        <div class="fw-bold" style="font-size: 15px;">INDO FITNESS GYM SPORT</div>
        <div>Tondano, Minahasa, Sulawesi Utara</div>
        <div>Telp / WA: 0812-3456-7890</div>
    </div>

    <div class="border-top border-bottom my-2" style="font-size: 12px;">
        <div class="d-flex">
            <span>No. Trx:</span>
            <span class="fw-bold">{{ $transaction->invoice_number }}</span>
        </div>
        <div class="d-flex">
            <span>Tanggal:</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="d-flex">
            <span>Kasir:</span>
            <span>{{ $transaction->user?->name ?? 'Kasir' }}</span>
        </div>
        <div class="d-flex">
            <span>Member:</span>
            <span class="fw-bold">{{ $transaction->member?->user?->name }}</span>
        </div>
        <div class="d-flex">
            <span>Kode Member:</span>
            <span>{{ $transaction->member?->member_code }}</span>
        </div>
    </div>

    <div class="my-2">
        @foreach ($transaction->items as $item)
            <div>
                <div class="fw-bold">{{ $item->product_name }}</div>
                <div class="d-flex">
                    <span>{{ $item->quantity }} x {{ $item->formatted_price }}</span>
                    <span>{{ $item->formatted_subtotal }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-top my-2 pt-2">
        <div class="d-flex fw-bold">
            <span>TOTAL:</span>
            <span>{{ $transaction->formatted_total_amount }}</span>
        </div>
        <div class="d-flex">
            <span>Pembayaran ({{ $transaction->paymentMethod?->name }}):</span>
            <span>{{ $transaction->formatted_paid_amount }}</span>
        </div>
        @if ($transaction->paymentMethod?->code === 'cash')
            <div class="d-flex">
                <span>Kembalian:</span>
                <span>{{ $transaction->formatted_change_amount }}</span>
            </div>
        @endif
    </div>

    @if ($transaction->membership)
        <div class="border-top border-bottom my-2 py-2" style="font-size: 12px;">
            <div class="fw-bold text-center">HAK AKSES MEMBERSHIP</div>
            <div class="d-flex">
                <span>Paket:</span>
                <span>{{ $transaction->membership->product?->name }}</span>
            </div>
            <div class="d-flex">
                <span>Masa Berlaku:</span>
                <span>{{ $transaction->membership->start_date->format('d/m/y') }} -
                    {{ $transaction->membership->end_date->format('d/m/y') }}</span>
            </div>
            <div class="d-flex">
                <span>Status:</span>
                <span class="fw-bold">{{ $transaction->membership->status_label }}</span>
            </div>
        </div>
    @endif

    <div class="text-center mt-3" style="font-size: 11px;">
        <div>Terima Kasih Atas Kunjungan Anda!</div>
        <div>Stay Fit, Stay Strong with IFGS Gym</div>
    </div>
</body>

</html>
