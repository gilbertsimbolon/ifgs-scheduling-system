<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    //
    /**
     * Menampilkan antarmuka POS (Point of Sale) untuk transaksi kasir.
     */
    public function index(Request $request): View
    {
        $members = Member::with(['user', 'memberships' => function ($q) {
            $q->where('status', Membership::STATUS_ACTIVE)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->latest('end_date');
        }])
            ->whereHas('user', function ($q) {
                $q->where('status', User::STATUS_ACTIVE);
            })
            ->get()
            ->sortBy(fn ($m) => strtolower($m->user?->name ?? ''));

        $products = Product::where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $todayStats = [
            'count' => Transaction::whereDate('created_at', today())->count(),
            'revenue' => Transaction::whereDate('created_at', today())
                ->where('status', Transaction::STATUS_COMPLETED)
                ->sum('total_amount'),
        ];

        $recentTransactions = Transaction::with(['member.user', 'paymentMethod', 'items', 'membership.product'])
            ->latest()
            ->take(5)
            ->get();

        $lastTransaction = null;
        if (session('last_transaction_id')) {
            $lastTransaction = Transaction::with(['member.user', 'paymentMethod', 'items.product', 'membership.product', 'user'])
                ->find(session('last_transaction_id'));
        }

        return view('pos.index', compact(
            'members',
            'products',
            'paymentMethods',
            'todayStats',
            'recentTransactions',
            'lastTransaction'
        ));
    }

    /**
     * AJAX endpoint untuk menghitung estimasi tanggal berakhir dan harga paket pada POS.
     */
    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'start_date' => ['required', 'date'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $endDate = $product->calculateEndDate($request->start_date)->format('Y-m-d');

        return response()->json([
            'end_date' => $endDate,
            'price' => (float) $product->price,
            'formatted_price' => $product->formatted_price,
            'duration_formatted' => $product->duration_formatted,
            'product_name' => $product->name,
        ]);
    }

    /**
     * Memproses transaksi POS, membuat snapshot transaksi, dan menerbitkan membership.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'product_id' => ['required', 'exists:products,id'],
            'payment_method_id' => [
                'required',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('status', PaymentMethod::STATUS_ACTIVE);
                }),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.exists' => 'Member tidak ditemukan.',
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak ditemukan.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid atau tidak aktif.',
            'start_date.required' => 'Tanggal mulai layanan wajib ditentukan.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal berakhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            'paid_amount.numeric' => 'Nominal bayar harus berupa angka.',
            'paid_amount.min' => 'Nominal bayar tidak boleh bernilai negatif.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->status !== Product::STATUS_ACTIVE) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Paket layanan yang dipilih sedang tidak aktif.');
        }

        $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);

        if ($paymentMethod->status !== PaymentMethod::STATUS_ACTIVE) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Metode pembayaran yang dipilih sedang tidak aktif.');
        }

        // Kalkulasi tanggal berakhir jika tidak ditentukan manual
        $endDate = $validated['end_date'] ?? $product->calculateEndDate($validated['start_date'])->format('Y-m-d');

        // Price snapshot dari produk
        $productPrice = (float) $product->price;
        $totalAmount = $productPrice;

        // Penentuan jumlah uang yang dibayarkan dan kembalian
        if ($paymentMethod->code === 'cash') {
            $paidAmount = $request->filled('paid_amount') ? (float) $request->paid_amount : $totalAmount;
            if ($paidAmount < $totalAmount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Nominal uang yang dibayarkan kurang dari total tagihan.');
            }
            $changeAmount = $paidAmount - $totalAmount;
        } else {
            // Untuk metode non-tunai (Transfer Bank, QRIS), pembayaran dianggap pas
            $paidAmount = $totalAmount;
            $changeAmount = 0.00;
        }

        // Eksekusi atomic transaksi dan penerbitan membership
        $transaction = DB::transaction(function () use (
            $validated,
            $product,
            $paymentMethod,
            $productPrice,
            $totalAmount,
            $paidAmount,
            $changeAmount,
            $endDate
        ) {
            // 1. Catat Transaksi Header
            $trx = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'member_id' => $validated['member_id'],
                'user_id' => auth()->id(),
                'payment_method_id' => $paymentMethod->id,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => Transaction::STATUS_COMPLETED,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 2. Catat Item Transaksi (Snapshot Nama & Harga)
            $trx->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $productPrice,
                'quantity' => 1,
                'subtotal' => $totalAmount,
            ]);

            // 3. Terbitkan Hasil Membership dari Transaksi
            Membership::create([
                'transaction_id' => $trx->id,
                'member_id' => $validated['member_id'],
                'product_id' => $product->id,
                'payment_method_id' => $paymentMethod->id,
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'price' => $totalAmount,
                'status' => Membership::STATUS_ACTIVE,
            ]);

            return $trx;
        });

        return redirect()->route('pos.index')
            ->with('success', "Transaksi berhasil disimpan! Nomor Invoice: {$transaction->invoice_number}")
            ->with('last_transaction_id', $transaction->id);
    }

    /**
     * Menampilkan struk/nota transaksi untuk cetak.
     */
    public function receipt(Transaction $transaction): View
    {
        $transaction->load(['member.user', 'paymentMethod', 'items.product', 'membership.product', 'user']);

        return view('pos.receipt', compact('transaction'));
    }
}
