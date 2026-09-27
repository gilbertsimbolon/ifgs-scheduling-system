<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar paket / layanan gym.
     */
    public function index(): View
    {
        $products = Product::with(['durations' => fn ($q) => $q->orderBy('duration_value')])
            ->withCount('memberships')
            ->orderBy('id')
            ->paginate(10);
        $durationUnits = ProductDuration::DURATION_UNITS;
        $statuses = Product::STATUSES;

        return view('product.index', compact('products', 'durationUnits', 'statuses'));
    }

    /**
     * Menyimpan data paket / layanan gym baru dengan pilihan durasi & harga.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->has('durations') && $request->filled('duration_value') && $request->filled('duration_unit')) {
            $request->merge([
                'durations' => [
                    [
                        'duration_value' => (int) $request->input('duration_value'),
                        'duration_unit' => $request->input('duration_unit'),
                        'price' => (float) $request->input('price', 0),
                    ],
                ],
            ]);
        }

        $durations = $request->input('durations');
        if (is_array($durations)) {
            foreach ($durations as $i => $item) {
                if (($item['duration_unit'] ?? '') === ProductDuration::DURATION_LIFETIME) {
                    $durations[$i]['duration_value'] = 0;
                }
            }
            $request->merge(['durations' => $durations]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(Product::STATUSES)],
            'durations' => ['required', 'array', 'min:1'],
            'durations.*.duration_value' => ['required', 'integer', 'min:0', 'max:365'],
            'durations.*.duration_unit' => ['required', 'string', Rule::in(ProductDuration::DURATION_UNITS)],
            'durations.*.price' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Nama paket layanan wajib diisi.',
            'name.max' => 'Nama paket layanan maksimal 255 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'durations.required' => 'Minimal harus menambahkan 1 pilihan durasi & harga.',
            'durations.min' => 'Minimal harus menambahkan 1 pilihan durasi & harga.',
            'durations.*.duration_value.required' => 'Durasi wajib diisi.',
            'durations.*.duration_value.min' => 'Durasi minimal bernilai 0.',
            'durations.*.duration_unit.required' => 'Satuan durasi wajib dipilih.',
            'durations.*.duration_unit.in' => 'Satuan durasi tidak valid.',
            'durations.*.price.required' => 'Harga wajib diisi.',
            'durations.*.price.numeric' => 'Harga harus berupa angka.',
            'durations.*.price.min' => 'Harga tidak boleh negatif.',
        ]);

        // Cek durasi > 0 untuk selain Seumur Hidup
        foreach ($validated['durations'] as $idx => $d) {
            if ($d['duration_unit'] !== ProductDuration::DURATION_LIFETIME && (int) $d['duration_value'] < 1) {
                return back()->withInput()->withErrors([
                    "durations.{$idx}.duration_value" => 'Durasi minimal bernilai 1 untuk satuan selain Seumur Hidup.',
                ]);
            }
        }

        // Cek kombinasi durasi duplikat
        $combinations = [];
        foreach ($validated['durations'] as $d) {
            $key = ($d['duration_unit'] === ProductDuration::DURATION_LIFETIME)
                ? 'lifetime'
                : ($d['duration_value'].'_'.$d['duration_unit']);

            if (in_array($key, $combinations, true)) {
                return back()->withInput()->withErrors([
                    'durations' => 'Terdapat pilihan kombinasi durasi dan satuan yang duplikat dalam paket ini.',
                ]);
            }
            $combinations[] = $key;
        }

        DB::transaction(function () use ($validated) {
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            foreach ($validated['durations'] as $d) {
                $product->durations()->create([
                    'duration_value' => $d['duration_value'],
                    'duration_unit' => $d['duration_unit'],
                    'price' => $d['price'],
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'Paket layanan baru berhasil ditambahkan.');
    }

    /**
     * Mengambil data paket beserta pilihan durasi & harga (untuk AJAX / modal edit).
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $product->load(['durations' => fn ($q) => $q->orderBy('duration_value')]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'durations' => $product->durations->map(fn ($d) => [
                'id' => $d->id,
                'duration_value' => $d->duration_value,
                'duration_unit' => $d->duration_unit,
                'duration_unit_label' => $d->duration_unit_label,
                'duration_formatted' => $d->duration_formatted,
                'price' => (float) $d->price,
                'formatted_price' => $d->formatted_price,
                'is_active' => $d->is_active,
            ]),
        ]);
    }

    /**
     * Memperbarui data paket / layanan gym beserta pilihan durasi & harganya.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        if (! $request->has('durations') && $request->filled('duration_value') && $request->filled('duration_unit')) {
            $request->merge([
                'durations' => [
                    [
                        'id' => $product->durations()->first()?->id,
                        'duration_value' => (int) $request->input('duration_value'),
                        'duration_unit' => $request->input('duration_unit'),
                        'price' => (float) $request->input('price', 0),
                    ],
                ],
            ]);
        }

        $durations = $request->input('durations');
        if (is_array($durations)) {
            foreach ($durations as $i => $item) {
                if (($item['duration_unit'] ?? '') === ProductDuration::DURATION_LIFETIME) {
                    $durations[$i]['duration_value'] = 0;
                }
            }
            $request->merge(['durations' => $durations]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(Product::STATUSES)],
            'durations' => ['required', 'array', 'min:1'],
            'durations.*.id' => ['nullable', 'exists:product_durations,id'],
            'durations.*.duration_value' => ['required', 'integer', 'min:0', 'max:365'],
            'durations.*.duration_unit' => ['required', 'string', Rule::in(ProductDuration::DURATION_UNITS)],
            'durations.*.price' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Nama paket layanan wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'durations.required' => 'Minimal harus menambahkan 1 pilihan durasi & harga.',
            'durations.min' => 'Minimal harus menambahkan 1 pilihan durasi & harga.',
            'durations.*.duration_value.required' => 'Durasi wajib diisi.',
            'durations.*.duration_unit.required' => 'Satuan durasi wajib dipilih.',
            'durations.*.price.required' => 'Harga wajib diisi.',
        ]);

        // Cek durasi > 0 untuk selain Seumur Hidup
        foreach ($validated['durations'] as $idx => $d) {
            if ($d['duration_unit'] !== ProductDuration::DURATION_LIFETIME && (int) $d['duration_value'] < 1) {
                return back()->withInput()->withErrors([
                    "durations.{$idx}.duration_value" => 'Durasi minimal bernilai 1 untuk satuan selain Seumur Hidup.',
                ]);
            }
        }

        // Cek kombinasi durasi duplikat
        $combinations = [];
        foreach ($validated['durations'] as $d) {
            $key = ($d['duration_unit'] === ProductDuration::DURATION_LIFETIME)
                ? 'lifetime'
                : ($d['duration_value'].'_'.$d['duration_unit']);

            if (in_array($key, $combinations, true)) {
                return back()->withInput()->withErrors([
                    'durations' => 'Terdapat pilihan kombinasi durasi dan satuan yang duplikat dalam paket ini.',
                ]);
            }
            $combinations[] = $key;
        }

        DB::transaction(function () use ($product, $validated) {
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            $keptIds = [];
            foreach ($validated['durations'] as $d) {
                if (! empty($d['id'])) {
                    $existing = $product->durations()->where('id', $d['id'])->first();
                    if ($existing) {
                        $existing->update([
                            'duration_value' => $d['duration_value'],
                            'duration_unit' => $d['duration_unit'],
                            'price' => $d['price'],
                            'is_active' => true,
                        ]);
                        $keptIds[] = $existing->id;

                        continue;
                    }
                }

                $newDuration = $product->durations()->create([
                    'duration_value' => $d['duration_value'],
                    'duration_unit' => $d['duration_unit'],
                    'price' => $d['price'],
                    'is_active' => true,
                ]);
                $keptIds[] = $newDuration->id;
            }

            // Hapus atau nonaktifkan durasi yang dihilangkan
            $removedDurations = $product->durations()->whereNotIn('id', $keptIds)->get();
            foreach ($removedDurations as $rem) {
                if ($rem->memberships()->exists() || $rem->transactionItems()->exists()) {
                    $rem->update(['is_active' => false]);
                } else {
                    $rem->delete();
                }
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'Paket layanan dan pilihan durasi berhasil diperbarui.');
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
     * Menghapus paket jika belum pernah digunakan dalam membership atau transaksi.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->memberships()->exists() || $product->transactionItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', "Paket '{$product->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi membership. Anda dapat mengubah statusnya menjadi Non-Aktif.");
        }

        $productName = $product->name;
        $product->durations()->delete();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Paket layanan '{$productName}' berhasil dihapus.");
    }
}
