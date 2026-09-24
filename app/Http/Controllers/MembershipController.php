<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    /**
     * Menampilkan daftar transaksi dan langganan membership.
     */
    public function index(Request $request): View
    {
        // Sinkronisasi otomatis status kadaluarsa oleh sistem
        Membership::where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['status' => Membership::STATUS_EXPIRED]);

        $query = Membership::with(['member.user', 'product', 'paymentMethod', 'transaction.user'])->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, Membership::STATUSES)) {
            $query->where('status', $request->status);
        }

        // Filter paket layanan (dinamis dari master produk)
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter metode pembayaran
        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Pencarian (Nama member, kode member, nama produk, atau nomor invoice)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('member.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($tq) use ($search) {
                        $tq->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $memberships = $query->paginate(10)->withQueryString();

        // Metrics untuk ringkasan di atas tabel
        $metrics = [
            'total' => Membership::count(),
            'pending' => Membership::where('status', Membership::STATUS_PENDING)->count(),
            'active' => Membership::where('status', Membership::STATUS_ACTIVE)
                ->whereDate('end_date', '>=', now())
                ->count(),
            'expired' => Membership::where('status', Membership::STATUS_EXPIRED)
                ->orWhere(function ($q) {
                    $q->where('status', Membership::STATUS_ACTIVE)
                        ->whereDate('end_date', '<', now());
                })->count(),
            'cancelled' => Membership::whereIn('status', [Membership::STATUS_CANCELLED, Membership::STATUS_REJECTED])->count(),
            'revenue' => Membership::where('status', Membership::STATUS_ACTIVE)->sum('price'),
        ];

        // Daftar member aktif untuk pilihan dropdown Tambah Membership
        $activeMembers = Member::with('user')
            ->whereHas('user', function ($q) {
                $q->where('status', User::STATUS_ACTIVE);
            })
            ->get()
            ->sortBy(fn ($m) => strtolower($m->user?->name ?? ''));

        // Daftar produk aktif untuk pilihan dropdown Tambah Membership
        $activeProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // Daftar metode pembayaran aktif untuk pilihan dropdown Tambah Membership
        $activePaymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $allPaymentMethods = PaymentMethod::orderBy('name')->get();
        $allProducts = Product::orderBy('name')->get();
        $products = $allProducts;
        $statuses = Membership::STATUSES;

        return view('membership.index', compact(
            'memberships',
            'metrics',
            'activeMembers',
            'activeProducts',
            'allProducts',
            'products',
            'activePaymentMethods',
            'allPaymentMethods',
            'statuses'
        ));
    }

    /**
     * Menyimpan data transaksi membership baru.
     * Status ditentukan secara otomatis oleh sistem, transaksi dicatat dengan pembuat (kasir/pelanggan).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'product_id' => ['required', 'exists:products,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.exists' => 'Member yang dipilih tidak valid.',
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak ditemukan.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            'price.numeric' => 'Biaya transaksi harus berupa angka.',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $endDate = ! empty($validated['end_date'])
            ? $validated['end_date']
            : $product->calculateEndDate($validated['start_date'])->format('Y-m-d');
        $price = isset($validated['price']) && $validated['price'] !== ''
            ? (float) $validated['price']
            : (float) $product->price;

        // Status diatur 100% oleh sistem berdasarkan tanggal berakhir
        $status = Carbon::parse($endDate)->isPast() && ! Carbon::parse($endDate)->isToday()
            ? Membership::STATUS_EXPIRED
            : Membership::STATUS_ACTIVE;

        DB::transaction(function () use ($validated, $product, $endDate, $price, $status) {
            // 1. Catat Transaksi Header dengan user_id pembuat (kasir atau pelanggan)
            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'member_id' => $validated['member_id'],
                'user_id' => auth()->id(),
                'payment_method_id' => $validated['payment_method_id'],
                'total_amount' => $price,
                'paid_amount' => $price,
                'change_amount' => 0.00,
                'status' => Transaction::STATUS_COMPLETED,
                'notes' => 'Pendaftaran Membership '.$product->name,
            ]);

            // 2. Catat Item Transaksi
            $transaction->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ]);

            // 3. Terbitkan Membership Baru
            Membership::create([
                'transaction_id' => $transaction->id,
                'member_id' => $validated['member_id'],
                'product_id' => $product->id,
                'payment_method_id' => $validated['payment_method_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $endDate,
                'price' => $price,
                'status' => $status,
            ]);
        });

        return redirect()->route('memberships.index')
            ->with('success', 'Transaksi membership baru berhasil ditambahkan.');
    }

    /**
     * AJAX endpoint untuk menghitung tanggal berakhir dan harga paket secara dinamis.
     */
    public function calculateEndDate(Request $request): JsonResponse
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
        ]);
    }

    /**
     * Memperbarui data membership.
     */
    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(Membership::STATUSES)],
        ], [
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak ditemukan.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal berakhir wajib diisi.',
            'end_date.date' => 'Format tanggal berakhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $membership->update($validated);

        return redirect()->route('memberships.index')
            ->with('success', 'Data membership berhasil diperbarui.');
    }

    /**
     * Membatalkan transaksi membership.
     */
    public function cancel(Request $request, Membership $membership): RedirectResponse|JsonResponse
    {
        $membership->update(['status' => Membership::STATUS_CANCELLED]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status membership berhasil diubah menjadi Dibatalkan.',
            ]);
        }

        return redirect()->route('memberships.index')
            ->with('success', 'Membership berhasil dibatalkan.');
    }

    /**
     * Memproses pemesanan paket membership mandiri oleh member (dengan upload bukti transfer).
     */
    public function order(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'start_date' => ['nullable', 'date'],
            'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak valid.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib dipilih.',
            'payment_proof.required' => 'Foto atau screenshot bukti transfer wajib diunggah.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa file gambar.',
            'payment_proof.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'payment_proof.max' => 'Ukuran file gambar maksimal 5MB.',
        ]);

        $startDate = ! empty($validated['start_date']) ? $validated['start_date'] : now()->toDateString();

        $user = $request->user();
        $member = $user->member;
        if (! $member) {
            $member = Member::create([
                'user_id' => $user->id,
                'member_code' => $user->user_code ?? Member::generateUniqueMemberCode(),
                'phone' => '',
            ]);
        }

        $product = Product::findOrFail($validated['product_id']);
        $endDate = $product->calculateEndDate($startDate)->format('Y-m-d');
        $price = (float) $product->price;

        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

        DB::transaction(function () use ($validated, $member, $product, $startDate, $endDate, $price, $proofPath) {
            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'member_id' => $member->id,
                'user_id' => null,
                'payment_method_id' => $validated['payment_method_id'],
                'total_amount' => $price,
                'paid_amount' => $price,
                'change_amount' => 0.00,
                'status' => Transaction::STATUS_PENDING,
                'notes' => $validated['notes'] ?? 'Pemesanan Mandiri Paket '.$product->name,
                'payment_proof' => $proofPath,
            ]);

            $transaction->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ]);

            Membership::create([
                'transaction_id' => $transaction->id,
                'member_id' => $member->id,
                'product_id' => $product->id,
                'payment_method_id' => $validated['payment_method_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $price,
                'status' => Membership::STATUS_PENDING,
            ]);
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan membership berhasil dikirim! Bukti transfer Anda sedang menunggu validasi oleh kasir.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Pesanan membership berhasil dikirim! Bukti transfer Anda sedang menunggu validasi oleh kasir.');
    }

    /**
     * Menyetujui (ACC) transaksi membership oleh kasir/admin.
     */
    public function approve(Request $request, Membership $membership): RedirectResponse|JsonResponse
    {
        $startDate = $membership->start_date ? $membership->start_date->format('Y-m-d') : now()->toDateString();
        if (Carbon::parse($startDate)->isPast() && ! Carbon::parse($startDate)->isToday()) {
            $startDate = now()->toDateString();
        }
        $endDate = $membership->product->calculateEndDate($startDate)->format('Y-m-d');

        DB::transaction(function () use ($membership, $startDate, $endDate) {
            if ($membership->transaction) {
                $membership->transaction->update([
                    'status' => Transaction::STATUS_COMPLETED,
                    'user_id' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }

            $membership->update([
                'status' => Membership::STATUS_ACTIVE,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        });

        $memberName = $membership->member?->user?->name ?? 'Member';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi membership untuk {$memberName} berhasil disetujui (ACC) dan aktif.",
            ]);
        }

        return redirect()->route('memberships.index')
            ->with('success', "Transaksi membership untuk {$memberName} berhasil disetujui (ACC) dan aktif.");
    }

    /**
     * Menolak transaksi membership oleh kasir/admin.
     */
    public function reject(Request $request, Membership $membership): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($membership, $validated) {
            if ($membership->transaction) {
                $membership->transaction->update([
                    'status' => Transaction::STATUS_REJECTED,
                    'user_id' => auth()->id(),
                    'rejection_reason' => $validated['reason'],
                ]);
            }

            $membership->update([
                'status' => Membership::STATUS_REJECTED,
            ]);
        });

        $memberName = $membership->member?->user?->name ?? 'Member';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi membership untuk {$memberName} telah ditolak.",
            ]);
        }

        return redirect()->route('memberships.index')
            ->with('success', "Transaksi membership untuk {$memberName} telah ditolak.");
    }

    /**
     * Menghapus riwayat data membership.
     */
    public function destroy(Membership $membership): RedirectResponse
    {
        $memberName = $membership->member?->user?->name ?? 'Member';
        $membership->delete();

        return redirect()->route('memberships.index')
            ->with('success', "Data membership untuk {$memberName} berhasil dihapus.");
    }
}
