<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainerController extends Controller
{
    /**
     * Tampilkan daftar seluruh Trainer (Member dengan is_trainer = true).
     */
    public function index(Request $request): View
    {
        $query = Trainer::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('trainer_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($specialization = $request->input('specialization')) {
            $query->where('specialization', 'like', "%{$specialization}%");
        }

        if ($status = $request->input('status')) {
            $query->where('trainer_status', $status);
        }

        $trainers = $query->latest()->paginate(10)->withQueryString();

        $specializations = Trainer::whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->select('specialization')
            ->distinct()
            ->pluck('specialization');

        // Daftar Member terdaftar yang belum menjadi trainer (untuk modal pencarian akun)
        $eligibleMembers = Member::where('is_trainer', false)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $eligibleMembersJson = $eligibleMembers->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->user?->name ?? 'Member #' . $m->id,
                'email' => $m->user?->email ?? '-',
                'code' => $m->member_code ?? '-',
                'phone' => $m->phone ?? '',
                'initials' => strtoupper(substr($m->user?->name ?? 'M', 0, 2)),
            ];
        })->values();

        return view('trainer.index', compact('trainers', 'specializations', 'eligibleMembers', 'eligibleMembersJson'));
    }

    /**
     * Aktifkan status is_trainer pada akun member yang sudah terdaftar.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
        ], [
            'member_id.required' => 'Pilih akun member yang ingin diaktifkan sebagai trainer.',
            'member_id.exists' => 'Data akun member tidak ditemukan.',
        ]);

        $member = Member::with('user')->findOrFail($validated['member_id']);

        $member->update([
            'is_trainer' => true,
            'trainer_status' => Trainer::STATUS_ACTIVE, // Default status: Aktif
            'specialization' => $validated['specialization'] ?? $member->specialization ?? 'Fitness & Gym Trainer',
        ]);

        $name = $member->user?->name ?? 'Member';

        return redirect()->route('trainers.index')
            ->with('success', "Akun \"{$name}\" berhasil diaktifkan sebagai Trainer (Status: Aktif).");
    }

    /**
     * Perbarui data trainer (spesialisasi atau status).
     */
    public function update(Request $request, Trainer $trainer): RedirectResponse
    {
        $validated = $request->validate([
            'specialization' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [
            'status.required' => 'Status operasional wajib dipilih.',
        ]);

        $trainer->update([
            'specialization' => $validated['specialization'] ?? $trainer->specialization,
            'trainer_status' => $validated['status'],
        ]);

        $name = $trainer->user?->name ?? 'Trainer';

        return redirect()->route('trainers.index')
            ->with('success', "Data Trainer \"{$name}\" berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / non-aktif trainer (On/Off switch di tabel).
     */
    public function toggleStatus(Request $request, Trainer $trainer): RedirectResponse|JsonResponse
    {
        $newStatus = ($trainer->trainer_status ?? 'active') === 'active' ? 'inactive' : 'active';

        $trainer->update(['trainer_status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $trainerName = $trainer->user?->name ?? 'Trainer';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => "Status Trainer \"{$trainerName}\" berhasil {$label}.",
            ]);
        }

        return redirect()->route('trainers.index')
            ->with('success', "Status Trainer \"{$trainerName}\" berhasil {$label}.");
    }

    /**
     * Cabut status trainer (mengubah is_trainer menjadi false).
     */
    public function destroy(Trainer $trainer): RedirectResponse
    {
        $trainerName = $trainer->user?->name ?? 'Trainer';

        $trainer->update([
            'is_trainer' => false,
            'trainer_status' => 'inactive',
        ]);

        return redirect()->route('trainers.index')
            ->with('success', "Status Trainer untuk \"{$trainerName}\" berhasil dicabut.");
    }
}
