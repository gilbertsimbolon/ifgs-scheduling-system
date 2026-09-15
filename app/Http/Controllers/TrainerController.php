<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainerController extends Controller
{
    /**
     * Tampilkan daftar seluruh Trainer / Instruktur.
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
            $query->where('status', $status);
        }

        $trainers = $query->latest()->paginate(10)->withQueryString();
        $specializations = Trainer::select('specialization')->distinct()->pluck('specialization');

        return view('trainer.index', compact('trainers', 'specializations'));
    }

    /**
     * Simpan data trainer baru dan buat akun user terkait.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Trainer::STATUS_ACTIVE, Trainer::STATUS_INACTIVE])],
        ], [
            'name.required' => 'Nama trainer wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar pada akun lain.',
            'password.required' => 'Kata sandi akun wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'specialization.required' => 'Spesialisasi keahlian wajib diisi.',
            'status.required' => 'Status operasional wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] === Trainer::STATUS_ACTIVE ? User::STATUS_ACTIVE : User::STATUS_INACTIVE,
            ]);

            $user->assignRole('Trainer');

            Trainer::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'],
                'specialization' => $validated['specialization'],
                'bio' => $validated['bio'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('trainers.index')
            ->with('success', 'Data Trainer "' . $validated['name'] . '" berhasil ditambahkan beserta akun login.');
    }

    /**
     * Perbarui data trainer dan akun user terkait.
     */
    public function update(Request $request, Trainer $trainer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($trainer->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in([Trainer::STATUS_ACTIVE, Trainer::STATUS_INACTIVE])],
        ], [
            'name.required' => 'Nama trainer wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'specialization.required' => 'Spesialisasi keahlian wajib diisi.',
            'status.required' => 'Status operasional wajib dipilih.',
        ]);

        DB::transaction(function () use ($trainer, $validated) {
            $user = $trainer->user;
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'] === Trainer::STATUS_ACTIVE ? User::STATUS_ACTIVE : User::STATUS_INACTIVE,
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $trainer->update([
                'phone' => $validated['phone'],
                'specialization' => $validated['specialization'],
                'bio' => $validated['bio'],
                'status' => $validated['status'],
            ]);
        });

        return redirect()->route('trainers.index')
            ->with('success', 'Data Trainer "' . $validated['name'] . '" berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / non-aktif trainer.
     */
    public function toggleStatus(Trainer $trainer): RedirectResponse
    {
        $newStatus = $trainer->status === Trainer::STATUS_ACTIVE
            ? Trainer::STATUS_INACTIVE
            : Trainer::STATUS_ACTIVE;

        DB::transaction(function () use ($trainer, $newStatus) {
            $trainer->update(['status' => $newStatus]);
            $trainer->user->update([
                'status' => $newStatus === Trainer::STATUS_ACTIVE ? User::STATUS_ACTIVE : User::STATUS_INACTIVE,
            ]);
        });

        $label = $newStatus === Trainer::STATUS_ACTIVE ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('trainers.index')
            ->with('success', "Status Trainer \"{$trainer->user->name}\" berhasil {$label}.");
    }

    /**
     * Hapus data trainer dan akun user-nya.
     */
    public function destroy(Trainer $trainer): RedirectResponse
    {
        $trainerName = $trainer->user->name;

        DB::transaction(function () use ($trainer) {
            $user = $trainer->user;
            $trainer->delete();
            $user->delete();
        });

        return redirect()->route('trainers.index')
            ->with('success', "Trainer \"{$trainerName}\" beserta akunnya berhasil dihapus.");
    }
}
