@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->invoice_number)

@section('content')
    <div class="container-xxl flex-grow-1">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 mt-2 gap-2">
            <div>
                <h5 class="fw-bold py-2 mb-0">
                    <span class="text-muted fw-light">Transaksi /</span> Detail Transaksi
                </h5>
                <p class="text-muted mb-0">Rincian invoice transaksi {{ $transaction->invoice_number }}.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank" class="btn btn-primary">
                    <i class="bx bx-printer me-1"></i> Cetak Struk
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold">Invoice: {{ $transaction->invoice_number }}</h6>
                <span class="badge {{ $transaction->status_badge_class }}">{{ $transaction->status_label }}</span>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted small d-block">Tanggal:</span>
                        <span class="fw-bold">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted small d-block">Member:</span>
                        <span class="fw-bold">{{ $transaction->member?->user?->name }}</span>
                        ({{ $transaction->member?->member_code }})
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted small d-block">Kasir:</span>
                        <span class="fw-bold">{{ $transaction->user?->name ?? 'Sistem' }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted small d-block">Metode Pembayaran:</span>
                        <span class="badge bg-label-info">{{ $transaction->paymentMethod?->name }}</span>
                    </div>
                </div>

                <div class="table-responsive border rounded mb-4">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Item Layanan</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaction->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">{{ $item->formatted_price }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold">{{ $item->formatted_subtotal }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light border-top">
                            <tr>
                                <th colspan="3" class="text-end">Total Tagihan:</th>
                                <th class="text-end text-primary fs-5">{{ $transaction->formatted_total_amount }}</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end text-muted small">Nominal Uang Diterima:</th>
                                <th class="text-end text-muted small">{{ $transaction->formatted_paid_amount }}</th>
                            </tr>
                            @if ($transaction->paymentMethod?->code === 'cash')
                                <tr>
                                    <th colspan="3" class="text-end text-muted small">Kembalian:</th>
                                    <th class="text-end text-success">{{ $transaction->formatted_change_amount }}</th>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                @if ($transaction->membership)
                    <div class="alert alert-light border p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong><i class="bx bx-id-card me-1 text-primary"></i> Data Membership Diterbitkan:</strong>
                            <span
                                class="badge {{ $transaction->membership->status_badge_class }}">{{ $transaction->membership->status_label }}</span>
                        </div>
                        <div class="small text-muted">
                            Paket: <strong>{{ $transaction->membership->product?->name }}</strong> |
                            Masa Berlaku: <strong>{{ $transaction->membership->start_date->format('d M Y') }}</strong> s/d
                            <strong>{{ $transaction->membership->end_date->format('d M Y') }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
