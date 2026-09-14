<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    //
    /**
     * Menampilkan riwayat transaksi POS IFGS.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['member.user', 'paymentMethod', 'user', 'items', 'membership.product'])
            ->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, Transaction::STATUSES)) {
            $query->where('status', $request->status);
        }

        // Filter metode pembayaran
        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Filter tanggal transaksi
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pencarian: No Invoice, Nama Member, Kode Member
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('member.user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Metrics
        $metrics = [
            'total_count' => Transaction::count(),
            'total_revenue' => Transaction::where('status', Transaction::STATUS_COMPLETED)->sum('total_amount'),
            'today_count' => Transaction::whereDate('created_at', today())->count(),
            'today_revenue' => Transaction::whereDate('created_at', today())
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('total_amount'),
        ];

        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $statuses = Transaction::STATUSES;

        return view('transaction.index', compact(
            'transactions',
            'metrics',
            'paymentMethods',
            'statuses'
        ));
    }

    /**
     * Menampilkan detail informasi transaksi (bisa untuk AJAX modal atau view).
     */
    public function show(Request $request, Transaction $transaction): View|JsonResponse
    {
        $transaction->load(['member.user', 'paymentMethod', 'user', 'items.product', 'membership.product']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'member_name' => $transaction->member?->user?->name ?? '-',
                'member_code' => $transaction->member?->member_code ?? '-',
                'cashier_name' => $transaction->user?->name ?? 'Sistem',
                'payment_method' => $transaction->paymentMethod?->name ?? '-',
                'total_amount' => $transaction->formatted_total_amount,
                'paid_amount' => $transaction->formatted_paid_amount,
                'change_amount' => $transaction->formatted_change_amount,
                'status' => $transaction->status_label,
                'status_badge' => $transaction->status_badge_class,
                'created_at' => $transaction->created_at->format('d M Y, H:i'),
                'notes' => $transaction->notes,
                'items' => $transaction->items->map(fn ($item) => [
                    'product_name' => $item->product_name,
                    'price' => $item->formatted_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->formatted_subtotal,
                ]),
                'membership' => $transaction->membership ? [
                    'product_name' => $transaction->membership->product?->name ?? '-',
                    'start_date' => $transaction->membership->start_date?->format('d M Y') ?? '-',
                    'end_date' => $transaction->membership->end_date?->format('d M Y') ?? '-',
                    'status' => $transaction->membership->status_label,
                    'status_badge' => $transaction->membership->status_badge_class,
                ] : null,
            ]);
        }

        return view('transaction.show', compact('transaction'));
    }

    /**
     * Menampilkan struk/nota transaksi untuk cetak.
     */
    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['member.user', 'paymentMethod', 'items.product', 'membership.product', 'user']);

        return view('transaction.receipt', compact('transaction'));
    }
}
