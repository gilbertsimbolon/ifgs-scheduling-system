<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Pencarian (Nama, kode, nomor rekening, atau atas nama)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        $paymentMethods = $query->paginate(10)->withQueryString();
        $statuses = PaymentMethod::STATUSES;
        $types = PaymentMethod::TYPES;

        return view('payment_method.index', compact('paymentMethods', 'statuses', 'types'));
    }

    /**
     * Menyimpan data metode pembayaran baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Kode unik dibuat otomatis dari nama metode (misal: "Tunai" -> "tunai")
        $name = trim((string) $request->input('name'));
        $baseCode = Str::slug($name, '_') ?: 'metode';
        $code = $baseCode;
        $counter = 1;
        while (PaymentMethod::where('code', $code)->exists()) {
            $code = "{$baseCode}_{$counter}";
            $counter++;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(PaymentMethod::TYPES)],
            'code' => ['required', 'string', 'max:100', 'unique:payment_methods,code'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'status' => ['required', 'string', Rule::in(PaymentMethod::STATUSES)],
        ], [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'name.max' => 'Nama metode pembayaran maksimal 255 karakter.',
            'type.required' => 'Tipe metode pembayaran wajib dipilih.',
            'type.in' => 'Tipe metode pembayaran tidak valid.',
            'qr_image.image' => 'File QRIS harus berupa gambar.',
            'qr_image.mimes' => 'Format gambar QRIS harus jpeg, png, jpg, webp, atau svg.',
            'qr_image.max' => 'Ukuran gambar QRIS maksimal 2MB.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        if ($request->hasFile('qr_image')) {
            $validated['qr_image'] = $request->file('qr_image')->store('payment_methods', 'public');
        }

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Metode pembayaran baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data metode pembayaran.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        // Kode unik diperbarui otomatis dari nama metode
        $name = trim((string) $request->input('name'));
        $baseCode = Str::slug($name, '_') ?: 'metode';
        $code = $baseCode;
        $counter = 1;
        while (PaymentMethod::where('code', $code)->where('id', '!=', $paymentMethod->id)->exists()) {
            $code = "{$baseCode}_{$counter}";
            $counter++;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(PaymentMethod::TYPES)],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id),
            ],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'status' => ['required', 'string', Rule::in(PaymentMethod::STATUSES)],
        ], [
            'name.required' => 'Nama metode pembayaran wajib diisi.',
            'name.max' => 'Nama metode pembayaran maksimal 255 karakter.',
            'type.required' => 'Tipe metode pembayaran wajib dipilih.',
            'type.in' => 'Tipe metode pembayaran tidak valid.',
            'qr_image.image' => 'File QRIS harus berupa gambar.',
            'qr_image.mimes' => 'Format gambar QRIS harus jpeg, png, jpg, webp, atau svg.',
            'qr_image.max' => 'Ukuran gambar QRIS maksimal 2MB.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        if ($request->hasFile('qr_image')) {
            // Hapus gambar lama jika tersimpan di disk public
            if ($paymentMethod->qr_image && Storage::disk('public')->exists($paymentMethod->qr_image)) {
                Storage::disk('public')->delete($paymentMethod->qr_image);
            }
            $validated['qr_image'] = $request->file('qr_image')->store('payment_methods', 'public');
        }

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
