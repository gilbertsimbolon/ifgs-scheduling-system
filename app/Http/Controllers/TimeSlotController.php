<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeSlotController extends Controller
{
    /**
     * Tampilkan daftar master data Jadwal Operasional / Time Slot.
     */
    public function index(Request $request): View
    {
        $query = TimeSlot::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('days', 'LIKE', "%{$search}%")
                    ->orWhere('start_time', 'LIKE', "%{$search}%")
                    ->orWhere('end_time', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category') && in_array($request->category, TimeSlot::CATEGORIES)) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status') && in_array($request->status, TimeSlot::STATUSES)) {
            $query->where('status', $request->status);
        }

        $timeSlots = $query->orderBy('start_time')->paginate(10)->withQueryString();

        $metrics = [
            'total' => TimeSlot::count(),
            'active' => TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)->count(),
            'inactive' => TimeSlot::where('status', TimeSlot::STATUS_INACTIVE)->count(),
            'total_capacity' => TimeSlot::where('status', TimeSlot::STATUS_ACTIVE)->sum('capacity'),
        ];

        $categories = TimeSlot::CATEGORIES;

        return view('time_slot.index', compact('timeSlots', 'metrics', 'categories'));
    }

    /**
     * Simpan master data Time Slot baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in(TimeSlot::CATEGORIES)],
            'days' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama sesi / slot wajib diisi.',
            'category.required' => 'Kategori layanan wajib dipilih.',
            'category.in' => 'Kategori layanan tidak valid.',
            'days.required' => 'Hari operasional wajib diisi.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai harus HH:mm (contoh: 08:00).',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai harus HH:mm (contoh: 09:00).',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'capacity.required' => 'Kapasitas maksimum wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1 orang.',
        ]);

        $validated['status'] = $validated['status'] ?? TimeSlot::STATUS_ACTIVE;

        TimeSlot::create($validated);

        return redirect()->route('time-slots.index')
            ->with('success', "Time Slot '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Perbarui master data Time Slot.
     */
    public function update(Request $request, TimeSlot $timeSlot): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in(TimeSlot::CATEGORIES)],
            'days' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ], [
            'name.required' => 'Nama sesi / slot wajib diisi.',
            'category.required' => 'Kategori layanan wajib dipilih.',
            'category.in' => 'Kategori layanan tidak valid.',
            'days.required' => 'Hari operasional wajib diisi.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai harus HH:mm (contoh: 08:00).',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai harus HH:mm (contoh: 09:00).',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'capacity.required' => 'Kapasitas maksimum wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1 orang.',
        ]);

        $timeSlot->update($validated);

        return redirect()->route('time-slots.index')
            ->with('success', "Time Slot '{$timeSlot->name}' berhasil diperbarui.");
    }

    /**
     * Ubah status aktif/non-aktif Time Slot via AJAX / Switch.
     */
    public function toggleStatus(Request $request, TimeSlot $timeSlot): RedirectResponse|JsonResponse
    {
        $newStatus = $timeSlot->status === TimeSlot::STATUS_ACTIVE
            ? TimeSlot::STATUS_INACTIVE
            : TimeSlot::STATUS_ACTIVE;

        $timeSlot->update(['status' => $newStatus]);
        $label = $newStatus === TimeSlot::STATUS_ACTIVE ? 'Aktif' : 'Non-Aktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'label' => $label,
                'message' => "Status Time Slot '{$timeSlot->name}' berhasil diubah menjadi {$label}.",
            ]);
        }

        return redirect()->route('time-slots.index')
            ->with('success', "Status Time Slot '{$timeSlot->name}' berhasil diubah menjadi {$label}.");
    }

    /**
     * Hapus master data Time Slot jika belum pernah memiliki riwayat reservasi atau jadwal.
     */
    public function destroy(TimeSlot $timeSlot): RedirectResponse
    {
        if ($timeSlot->schedules()->exists() || $timeSlot->reservations()->exists()) {
            return redirect()->route('time-slots.index')
                ->with('error', "Time Slot '{$timeSlot->name}' tidak dapat dihapus karena sudah memiliki riwayat kunjungan. Anda dapat mengubah statusnya menjadi Non-Aktif.");
        }

        $name = $timeSlot->name;
        $timeSlot->delete();

        return redirect()->route('time-slots.index')
            ->with('success', "Time Slot '{$name}' berhasil dihapus.");
    }
}
