<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar paket / layanan gym.
     */
    public function index(): View
    {
        $products = Product::withCount('memberships')->latest()->paginate(10);
        $durationUnits = Product::DURATION_UNITS;
        $statuses = Product::STATUSES;

        return view('product.index', compact('products', 'durationUnits', 'statuses'));
    }

    /**
     * Menyimpan data paket / layanan gym baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_value' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_unit' => ['required', 'string', Rule::in(Product::DURATION_UNITS)],
            'status' => ['required', 'string', Rule::in(Product::STATUSES)],
        ], [
            'name.required' => 'Nama paket layanan wajib diisi.',
            'name.max' => 'Nama paket layanan maksimal 255 karakter.',
            'price.required' => 'Harga paket wajib diisi.',
            'price.numeric' => 'Harga paket harus berupa angka.',
            'price.min' => 'Harga paket tidak boleh kurang dari 0.',
            'duration_value.required' => 'Durasi wajib diisi.',
            'duration_value.min' => 'Durasi minimal bernilai 1.',
            'duration_unit.required' => 'Satuan durasi wajib dipilih.',
            'duration_unit.in' => 'Satuan durasi tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Paket layanan berhasil ditambahkan.');
    }

    /**
     * Mengambil data paket (bisa untuk AJAX atau kalkulasi tanggal selesai).
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $product->calculateEndDate($startDate)->format('Y-m-d');

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'formatted_price' => $product->formatted_price,
            'duration_value' => $product->duration_value,
            'duration_unit' => $product->duration_unit,
            'duration_formatted' => $product->duration_formatted,
            'status' => $product->status,
            'calculated_end_date' => $endDate,
        ]);
    }

    /**
     * Memperbarui data paket / layanan gym.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_value' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_unit' => ['required', 'string', Rule::in(Product::DURATION_UNITS)],
            'status' => ['required', 'string', Rule::in(Product::STATUSES)],
        ], [
            'name.required' => 'Nama paket layanan wajib diisi.',
            'price.required' => 'Harga paket wajib diisi.',
            'price.numeric' => 'Harga paket harus berupa angka.',
            'duration_value.required' => 'Durasi wajib diisi.',
            'duration_unit.required' => 'Satuan durasi wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Paket layanan berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / tidak aktif paket.
     */
    public function toggleStatus(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $newStatus = $product->status === Product::STATUS_ACTIVE
            ? Product::STATUS_INACTIVE
            : Product::STATUS_ACTIVE;

        $product->update(['status' => $newStatus]);

        $label = $newStatus === Product::STATUS_ACTIVE ? 'Aktif' : 'Non-Aktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'label' => $label,
                'message' => "Status paket {$product->name} berhasil diubah menjadi {$label}.",
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', "Status paket {$product->name} berhasil diubah menjadi {$label}.");
    }

    /**
     * Menghapus paket jika belum pernah digunakan dalam membership.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->memberships()->exists()) {
            return redirect()->route('products.index')
                ->with('error', "Paket '{$product->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi membership. Anda dapat mengubah statusnya menjadi Non-Aktif.");
        }

        $productName = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Paket layanan '{$productName}' berhasil dihapus.");
    }
}
