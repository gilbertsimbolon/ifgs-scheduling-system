<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductDuration;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $query = Membership::with(['member.user', 'product', 'duration', 'paymentMethod', 'transaction.user'])->latest();

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

        // Daftar produk aktif beserta durasi aktifnya untuk pilihan dropdown Tambah Membership
        $activeProducts = Product::with(['activeDurations' => fn ($q) => $q->orderBy('duration_value')])
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // Daftar metode pembayaran aktif untuk pilihan dropdown Tambah Membership
        $activePaymentMethods = PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        $allPaymentMethods = PaymentMethod::orderBy('name')->get();
        $allProducts = Product::with(['durations' => fn ($q) => $q->orderBy('duration_value')])->orderBy('name')->get();
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
            'product_duration_id' => ['nullable', 'exists:product_durations,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.exists' => 'Member yang dipilih tidak valid.',
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak ditemukan.',
            'product_duration_id.exists' => 'Pilihan durasi yang dipilih tidak valid.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            'price.numeric' => 'Biaya transaksi harus berupa angka.',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        if (! empty($validated['product_duration_id'])) {
            $duration = ProductDuration::where('product_id', $product->id)->findOrFail($validated['product_duration_id']);
        } else {
            $duration = $product->activeDurations()->first() ?? $product->durations()->first();
        }

        $startDate = $validated['start_date'];

        // Cek sistem akumulasi durasi:
        // Jika start_date diisi hari ini (default) dan member sudah memiliki paket aktif yang sama dengan sisa durasi
        $latestActiveMembership = Membership::where('member_id', $validated['member_id'])
            ->where('product_id', $product->id)
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();

        $isAccumulated = false;
        if ($latestActiveMembership && Carbon::parse($startDate)->isToday() && Carbon::parse($latestActiveMembership->end_date)->gte(today())) {
            $startDate = Carbon::parse($latestActiveMembership->end_date)->addDay()->format('Y-m-d');
            $isAccumulated = true;
        }

        $endDate = ! empty($validated['end_date'])
            ? $validated['end_date']
            : ($duration ? $duration->calculateEndDate($startDate)->format('Y-m-d') : Carbon::parse($startDate)->addMonth()->format('Y-m-d'));
        $price = isset($validated['price']) && $validated['price'] !== ''
            ? (float) $validated['price']
            : (float) ($duration ? $duration->price : 0.0);

        // Status diatur 100% oleh sistem berdasarkan tanggal berakhir
        $status = Carbon::parse($endDate)->isPast() && ! Carbon::parse($endDate)->isToday()
            ? Membership::STATUS_EXPIRED
            : Membership::STATUS_ACTIVE;

        DB::transaction(function () use ($validated, $product, $duration, $startDate, $endDate, $price, $status, $isAccumulated) {
            $durationLabel = $duration?->duration_formatted ?? '';
            $notePrefix = $isAccumulated ? 'Perpanjangan (Akumulasi) Membership ' : 'Pendaftaran Membership ';

            // 1. Catat Transaksi Header dengan user_id pembuat (kasir atau admin)
            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'member_id' => $validated['member_id'],
                'user_id' => auth()->id(),
                'payment_method_id' => $validated['payment_method_id'],
                'total_amount' => $price,
                'paid_amount' => $price,
                'change_amount' => 0.00,
                'status' => Transaction::STATUS_COMPLETED,
                'notes' => $notePrefix.$product->name.($durationLabel ? " ({$durationLabel})" : ''),
            ]);

            // 2. Catat Item Transaksi (Snapshot price & duration reference)
            $transaction->items()->create([
                'product_id' => $product->id,
                'product_duration_id' => $duration?->id,
                'product_name' => $product->name.($durationLabel ? " ({$durationLabel})" : ''),
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ]);

            // 3. Terbitkan Membership Baru (Snapshot price & duration reference)
            Membership::create([
                'transaction_id' => $transaction->id,
                'member_id' => $validated['member_id'],
                'product_id' => $product->id,
                'product_duration_id' => $duration?->id,
                'payment_method_id' => $validated['payment_method_id'],
                'start_date' => $startDate,
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
            'start_date' => ['required', 'date'],
            'product_duration_id' => ['nullable', 'exists:product_durations,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'member_id' => ['nullable', 'exists:members,id'],
        ]);

        $startDate = $request->start_date;
        $memberId = $request->member_id;
        $isAccumulated = false;
        $activeEndDate = null;
        $daysRemaining = 0;

        $productId = $request->product_id;
        if (! $productId && $request->filled('product_duration_id')) {
            $duration = ProductDuration::find($request->product_duration_id);
            $productId = $duration?->product_id;
        }

        // Cek sistem akumulasi jika ada member_id & product_id
        if ($memberId && $productId) {
            $latestActive = Membership::where('member_id', $memberId)
                ->where('product_id', $productId)
                ->where('status', Membership::STATUS_ACTIVE)
                ->whereDate('end_date', '>=', now()->toDateString())
                ->orderByDesc('end_date')
                ->first();

            if ($latestActive && Carbon::parse($startDate)->isToday()) {
                $isAccumulated = true;
                $activeEndDate = $latestActive->end_date->format('Y-m-d');
                $daysRemaining = $latestActive->days_remaining;
                $startDate = Carbon::parse($latestActive->end_date)->addDay()->format('Y-m-d');
            }
        }

        if ($request->filled('product_duration_id')) {
            $duration = ProductDuration::findOrFail($request->product_duration_id);
            $endDate = $duration->calculateEndDate($startDate)->format('Y-m-d');

            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => (float) $duration->price,
                'formatted_price' => $duration->formatted_price,
                'duration_formatted' => $duration->duration_formatted,
                'is_accumulated' => $isAccumulated,
                'active_end_date' => $activeEndDate,
                'days_remaining' => $daysRemaining,
            ]);
        }

        if ($productId) {
            $product = Product::findOrFail($productId);
            $duration = $product->activeDurations()->first() ?? $product->durations()->first();
            if ($duration) {
                $endDate = $duration->calculateEndDate($startDate)->format('Y-m-d');

                return response()->json([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'price' => (float) $duration->price,
                    'formatted_price' => $duration->formatted_price,
                    'duration_formatted' => $duration->duration_formatted,
                    'is_accumulated' => $isAccumulated,
                    'active_end_date' => $activeEndDate,
                    'days_remaining' => $daysRemaining,
                ]);
            }
        }

        return response()->json(['error' => 'Pilihan paket atau durasi tidak valid.'], 422);
    }

    /**
     * Memperbarui data membership.
     */
    public function update(Request $request, Membership $membership): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_duration_id' => ['nullable', 'exists:product_durations,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(Membership::STATUSES)],
        ], [
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal berakhir wajib diisi.',
            'price.required' => 'Biaya membership wajib diisi.',
            'status.required' => 'Status membership wajib dipilih.',
        ]);

        if (empty($validated['product_duration_id'])) {
            $duration = ProductDuration::where('product_id', $validated['product_id'])->first();
            $validated['product_duration_id'] = $duration?->id;
        }

        $membership->update($validated);

        return redirect()->route('memberships.index')
            ->with('success', 'Data membership berhasil diperbarui.');
    }

    /**
     * Menyimpan pemesanan membership mandiri oleh Member (dengan upload bukti bayar).
     */
    public function order(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'product_duration_id' => ['nullable', 'exists:product_durations,id'],
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
        if (! empty($validated['product_duration_id'])) {
            $duration = ProductDuration::where('product_id', $product->id)->findOrFail($validated['product_duration_id']);
        } else {
            $duration = $product->activeDurations()->first() ?? $product->durations()->first();
        }

        // Cek sistem akumulasi durasi:
        // Jika member sudah memiliki paket aktif untuk produk ini yang belum kedaluwarsa, akumulasikan dari tanggal berakhir aktif
        $latestActiveMembership = Membership::where('member_id', $member->id)
            ->where('product_id', $product->id)
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();

        $isAccumulated = false;
        if ($latestActiveMembership && Carbon::parse($latestActiveMembership->end_date)->gte(today())) {
            $startDate = Carbon::parse($latestActiveMembership->end_date)->addDay()->format('Y-m-d');
            $isAccumulated = true;
        } else {
            $inputDate = ! empty($validated['start_date']) ? $validated['start_date'] : now()->toDateString();
            $startDate = Carbon::parse($inputDate)->lt(today()) ? now()->toDateString() : $inputDate;
        }

        $endDate = $duration ? $duration->calculateEndDate($startDate)->format('Y-m-d') : Carbon::parse($startDate)->addMonth()->format('Y-m-d');
        $price = $duration ? (float) $duration->price : 0.0;
        $durationFormatted = $duration?->duration_formatted ?? '';

        $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

        DB::transaction(function () use ($validated, $member, $product, $duration, $startDate, $endDate, $price, $durationFormatted, $proofPath, $isAccumulated) {
            $notePrefix = $isAccumulated ? 'Perpanjangan (Akumulasi) Paket ' : 'Pemesanan Mandiri Paket ';
            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'member_id' => $member->id,
                'user_id' => null,
                'payment_method_id' => $validated['payment_method_id'],
                'total_amount' => $price,
                'paid_amount' => $price,
                'change_amount' => 0.00,
                'status' => Transaction::STATUS_PENDING,
                'notes' => $validated['notes'] ?? ($notePrefix.$product->name.($durationFormatted ? " ({$durationFormatted})" : '')),
                'payment_proof' => $proofPath,
            ]);

            $transaction->items()->create([
                'product_id' => $product->id,
                'product_duration_id' => $duration?->id,
                'product_name' => $product->name.($durationFormatted ? " ({$durationFormatted})" : ''),
                'price' => $price,
                'quantity' => 1,
                'subtotal' => $price,
            ]);

            Membership::create([
                'transaction_id' => $transaction->id,
                'member_id' => $member->id,
                'product_id' => $product->id,
                'product_duration_id' => $duration?->id,
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
        $duration = $membership->duration ?? $membership->product->activeDurations()->first();

        // Cek sistem akumulasi: apakah member memiliki membership aktif untuk paket ini (di luar transaksi ini)
        $latestActiveMembership = Membership::where('member_id', $membership->member_id)
            ->where('product_id', $membership->product_id)
            ->where('id', '!=', $membership->id)
            ->where('status', Membership::STATUS_ACTIVE)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderByDesc('end_date')
            ->first();

        if ($latestActiveMembership && Carbon::parse($latestActiveMembership->end_date)->gte(today())) {
            // Akumulasi dari tanggal berakhir membership aktif
            $startDate = Carbon::parse($latestActiveMembership->end_date)->addDay()->format('Y-m-d');
        } else {
            // Jika membership sebelumnya sudah tersimpan dengan start_date hari ini atau masa depan, gunakan itu
            $storedStartDate = $membership->start_date ? $membership->start_date->format('Y-m-d') : null;
            if (! $storedStartDate || Carbon::parse($storedStartDate)->lt(today())) {
                $startDate = now()->toDateString();
            } else {
                $startDate = $storedStartDate;
            }
        }

        $endDate = $duration
            ? $duration->calculateEndDate($startDate)->format('Y-m-d')
            : ($membership->end_date ? Carbon::parse($membership->end_date)->format('Y-m-d') : Carbon::parse($startDate)->addMonth()->format('Y-m-d'));

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
     * Membatalkan membership aktif.
     */
    public function cancel(Membership $membership): RedirectResponse
    {
        $membership->update([
            'status' => Membership::STATUS_CANCELLED,
        ]);

        return redirect()->route('memberships.index')
            ->with('success', 'Membership berhasil dibatalkan.');
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
