<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles', 'member', 'trainer']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_code', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->role($role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::pluck('name');
        $statuses = User::STATUSES;

        return view('pengguna.index', compact('users', 'roles', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $availableRoles = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in($availableRoles)],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'slug' => User::generateUniqueSlug($validated['name']),
            ]);

            $user->assignRole($validated['role']);

            // Jika peran Member, otomatis buat profil Member dengan kode unik
            if ($validated['role'] === 'Member') {
                Member::create([
                    'user_id' => $user->id,
                    'phone' => $validated['phone'] ?? null,
                ]);
            }

            // Jika peran Trainer, otomatis buat profil Trainer
            if ($validated['role'] === 'Trainer') {
                Trainer::create([
                    'user_id' => $user->id,
                    'phone' => $validated['phone'] ?? null,
                    'status' => Trainer::STATUS_ACTIVE,
                ]);
            }
        });

        if ($request->user() && $request->user()->hasRole('Kasir')) {
            return redirect()->route('kasir.index')
                ->with('success', 'Pengguna berhasil ditambahkan.');
        }

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $availableRoles = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in($availableRoles)],
            'status' => ['sometimes', 'required', 'string', Rule::in(User::STATUSES)],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            if ($validated['name'] !== $user->name) {
                $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (isset($validated['status'])) {
                $user->status = $validated['status'];
            }

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->syncRoles([$validated['role']]);

            // Sinkronisasi nomor HP & pastikan profil Member ada jika role=Member
            if ($validated['role'] === 'Member') {
                $user->load('member');
                if ($user->member) {
                    $user->member->update(['phone' => $validated['phone'] ?? null]);
                } else {
                    Member::create([
                        'user_id' => $user->id,
                        'phone' => $validated['phone'] ?? null,
                    ]);
                }
            }

            // Sinkronisasi nomor HP & pastikan profil Trainer ada jika role=Trainer
            if ($validated['role'] === 'Trainer') {
                $user->load('trainer');
                if ($user->trainer) {
                    $user->trainer->update(['phone' => $validated['phone'] ?? null]);
                } else {
                    Trainer::create([
                        'user_id' => $user->id,
                        'phone' => $validated['phone'] ?? null,
                        'status' => Trainer::STATUS_ACTIVE,
                    ]);
                }
            }
        });

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $newStatus = $user->status === User::STATUS_ACTIVE
            ? User::STATUS_INACTIVE
            : User::STATUS_ACTIVE;

        $user->update(['status' => $newStatus]);

        $label = $newStatus === User::STATUS_ACTIVE ? 'Aktif' : 'Tidak Aktif';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'label' => $label,
                'message' => "Status pengguna {$user->name} berhasil diubah menjadi {$label}.",
            ]);
        }

        return redirect()->route('pengguna.index')
            ->with('success', "Status pengguna {$user->name} berhasil diubah menjadi {$label}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
