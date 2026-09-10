<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    /**
     * Menampilkan daftar transaksi dan langganan membership.
     */
    public function index(Request $request): View
    {
        $query = Membership::with(['member.user', 'product', 'paymentMethod'])->latest();

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

        // Pencarian (Nama member, kode member, atau nama produk)
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
                    });
            });
        }

        $memberships = $query->paginate(10)->withQueryString();

        // Metrics untuk ringkasan di atas tabel
        $metrics = [
            'total' => Membership::count(),
            'active' => Membership::where('status', Membership::STATUS_ACTIVE)
                ->whereDate('end_date', '>=', now())
                ->count(),
            'expired' => Membership::where('status', Membership::STATUS_EXPIRED)
                ->orWhere(function ($q) {
                    $q->where('status', Membership::STATUS_ACTIVE)
                        ->whereDate('end_date', '<', now());
                })->count(),
            'cancelled' => Membership::where('status', Membership::STATUS_CANCELLED)->count(),
            'revenue' => Membership::where('status', '!=', Membership::STATUS_CANCELLED)->sum('price'),
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
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(Membership::STATUSES)],
        ], [
            'member_id.required' => 'Member wajib dipilih.',
            'member_id.exists' => 'Member tidak ditemukan.',
            'product_id.required' => 'Paket layanan wajib dipilih.',
            'product_id.exists' => 'Paket layanan tidak ditemukan.',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid atau tidak aktif.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal berakhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal berakhir harus sama atau setelah tanggal mulai.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->status !== Product::STATUS_ACTIVE) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Paket layanan yang dipilih sedang tidak aktif.');
        }

        // Kalkulasi tanggal berakhir jika tidak diisi manual
        if (empty($validated['end_date'])) {
            $validated['end_date'] = $product->calculateEndDate($validated['start_date'])->format('Y-m-d');
        }

        // Snapshot harga saat transaksi jika tidak diinput manual
        if (! isset($validated['price']) || $validated['price'] === null || $validated['price'] === '') {
            $validated['price'] = $product->price;
        }

        $validated['status'] = $validated['status'] ?? Membership::STATUS_ACTIVE;

        Membership::create($validated);

        return redirect()->route('memberships.index')
            ->with('success', 'Transaksi membership berhasil ditambahkan.');
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
