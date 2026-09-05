<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $query = User::with('roles');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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
            'status' => ['required', 'string', Rule::in(User::STATUSES)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.required' => 'Peran wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
            'slug' => User::generateUniqueSlug($validated['name']),
        ]);

        $user->assignRole($validated['role']);

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
            'status' => ['required', 'string', Rule::in(User::STATUSES)],
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
        ]);

        if ($validated['name'] !== $user->name) {
            $user->slug = User::generateUniqueSlug($validated['name'], $user->id);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->status = $validated['status'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
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
