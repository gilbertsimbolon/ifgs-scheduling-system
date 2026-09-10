<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    /**
     * Menampilkan daftar master data metode pembayaran.
     */
    public function index(Request $request): View
    {
        $query = PaymentMethod::withCount('memberships')->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, PaymentMethod::STATUSES)) {
            $query->where('status', $request->status);
        }

        // Pencarian (Nama atau kode)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $paymentMethods = $query->paginate(10)->withQueryString();

        $metrics = [
            'total' => PaymentMethod::count(),
            'active' => PaymentMethod::where('status', PaymentMethod::STATUS_ACTIVE)->count(),
            'inactive' => PaymentMethod::where('status', PaymentMethod::STATUS_INACTIVE)->count(),
        ];

        $statuses = PaymentMethod::STATUSES;

        return view('payment_method.index', compact('paymentMethods', 'metrics', 'statuses'));
    }

    /**
     * Menyimpan data metode pembayaran baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Auto-generate code from name if code is empty
        if (! $request->filled('code') && $request->filled('name')) {
            $generatedCode = Str::slug($request->name, '_');
            $request->merge(['code' => $generatedCode]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:payment_methods,code'],
            'status' => ['required', 'string', Rule::in(PaymentMethod::STATUSES)],
        ], [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'name.max' => 'Nama metode pembayaran maksimal 255 karakter.',
            'code.required' => 'Kode metode pembayaran wajib diisi.',
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'code.unique' => 'Kode metode pembayaran sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data metode pembayaran.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id),
            ],
            'status' => ['required', 'string', Rule::in(PaymentMethod::STATUSES)],
        ], [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'name.max' => 'Nama metode pembayaran maksimal 255 karakter.',
            'code.required' => 'Kode metode pembayaran wajib diisi.',
            'code.regex' => 'Kode hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'code.unique' => 'Kode metode pembayaran sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', "Metode pembayaran '{$paymentMethod->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / tidak aktif metode pembayaran.
     */
    public function toggleStatus(Request $request, PaymentMethod $paymentMethod): RedirectResponse|JsonResponse
    {
        $newStatus = $paymentMethod->status === PaymentMethod::STATUS_ACTIVE
            ? PaymentMethod::STATUS_INACTIVE
            : PaymentMethod::STATUS_ACTIVE;

        $paymentMethod->update(['status' => $newStatus]);

        $label = $newStatus === PaymentMethod::STATUS_ACTIVE ? 'Aktif' : 'Non-Aktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'label' => $label,
                'message' => "Status metode pembayaran {$paymentMethod->name} berhasil diubah menjadi {$label}.",
            ]);
        }

        return redirect()->route('payment-methods.index')
            ->with('success', "Status metode pembayaran {$paymentMethod->name} berhasil diubah menjadi {$label}.");
    }

    /**
     * Menghapus metode pembayaran jika belum digunakan dalam transaksi.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->memberships()->exists()) {
            return redirect()->route('payment-methods.index')
                ->with('error', "Metode pembayaran '{$paymentMethod->name}' tidak dapat dihapus karena sudah digunakan dalam riwayat transaksi. Anda dapat mengubah statusnya menjadi Non-Aktif.");
        }

        $name = $paymentMethod->name;
        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', "Metode pembayaran '{$name}' berhasil dihapus.");
    }
}
