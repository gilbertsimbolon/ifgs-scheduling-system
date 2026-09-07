<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Menampilkan daftar member yang terdaftar pada sistem.
     */
    public function index(Request $request): View
    {
        $query = Member::with('user');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->whereHas('user', function ($userQuery) use ($status) {
                $userQuery->where('status', $status);
            });
        }

        $members = $query->latest()->paginate(10)->withQueryString();
        $statuses = User::STATUSES;

        return view('member.index', compact('members', 'statuses'));
    }

    /**
     * Menyimpan data member baru ke dalam sistem.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(User::STATUSES)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
                'slug' => User::generateUniqueSlug($validated['name']),
            ]);

            $user->assignRole('Member');

            Member::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('member.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    /**
     * Memperbarui data member pada sistem.
     */
    public function update(Request $request, Member $member): RedirectResponse
    {
        $user = $member->user;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(User::STATUSES)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        DB::transaction(function () use ($validated, $user, $member) {
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

            $member->update([
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('member.index')
            ->with('success', 'Data member berhasil diperbarui.');
    }

    /**
     * Toggle status aktif akun member.
     */
    public function toggleStatus(Request $request, Member $member): RedirectResponse|JsonResponse
    {
        $user = $member->user;

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
                'message' => "Status member {$user->name} berhasil diubah menjadi {$label}.",
            ]);
        }

        return redirect()->route('member.index')
            ->with('success', "Status member {$user->name} berhasil diubah menjadi {$label}.");
    }

    /**
     * Menghapus data member dari sistem.
     */
    public function destroy(Member $member): RedirectResponse
    {
        $user = $member->user;
        $memberName = $user ? $user->name : $member->member_code;

        if ($user) {
            $user->delete();
        } else {
            $member->delete();
        }

        return redirect()->route('member.index')
            ->with('success', "Member {$memberName} berhasil dihapus.");
    }
}
