<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TimeSlot;
use App\Models\Trainer;
use App\Models\TrainerBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainerBookingController extends Controller
{
    //
    /**
     * Tampilkan daftar permohonan sesi latihan trainer.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = TrainerBooking::with(['trainer.user', 'member.user', 'timeSlot'])->latest('session_date');

        // Scope jika yang login adalah Trainer (hanya melihat sesi yang diajukan ke dirinya)
        $currentTrainer = null;
        if ($user->hasRole('Trainer')) {
            $currentTrainer = $user->trainer;
            if ($currentTrainer) {
                $query->where('trainer_id', $currentTrainer->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('Member')) {
            // Scope jika yang login adalah Member
            $member = $user->member;
            if ($member) {
                $query->where('member_id', $member->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter status
        if ($request->filled('status') && in_array($request->status, TrainerBooking::STATUSES)) {
            $query->where('status', $request->status);
        }

        // Filter trainer (untuk admin/kasir)
        if ($request->filled('trainer_id') && ! $user->hasRole('Trainer')) {
            $query->where('trainer_id', $request->trainer_id);
        }

        // Filter tanggal sesi
        if ($request->filled('session_date')) {
            $query->whereDate('session_date', $request->session_date);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('training_focus', 'like', "%{$search}%")
                    ->orWhereHas('member.user', function ($mq) use ($search) {
                        $mq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trainer.user', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        // Metrics hitung sesi
        $metricsQuery = TrainerBooking::query();
        if ($user->hasRole('Trainer')) {
            if ($currentTrainer) {
                $metricsQuery->where('trainer_id', $currentTrainer->id);
            } else {
                $metricsQuery->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('Member')) {
            if ($user->member) {
                $metricsQuery->where('member_id', $user->member->id);
            } else {
                $metricsQuery->whereRaw('1 = 0');
            }
        }

        $metrics = [
            'pending' => (clone $metricsQuery)->where('status', TrainerBooking::STATUS_PENDING)->count(),
            'approved' => (clone $metricsQuery)->where('status', TrainerBooking::STATUS_APPROVED)->count(),
            'in_progress' => (clone $metricsQuery)->where('status', TrainerBooking::STATUS_IN_PROGRESS)->count(),
            'completed' => (clone $metricsQuery)->where('status', TrainerBooking::STATUS_COMPLETED)->count(),
            'rejected' => (clone $metricsQuery)->where('status', TrainerBooking::STATUS_REJECTED)->count(),
            'total' => (clone $metricsQuery)->count(),
        ];

        $trainers = Trainer::active()->with('user')->get();
        $timeSlots = TimeSlot::active()->orderBy('start_time')->get();
        $members = Member::with('user')->get();

        return view('trainer_booking.index', compact(
            'bookings',
            'metrics',
            'trainers',
            'timeSlots',
            'members',
            'currentTrainer'
        ));
    }

    /**
     * Simpan permohonan sesi latihan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $rules = [
            'trainer_id' => ['required', 'exists:trainers,id'],
            'time_slot_id' => ['nullable', 'exists:time_slots,id'],
            'session_date' => ['required', 'date', 'after_or_equal:today'],
            'training_focus' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Jika bukan member, member_id wajib dipilih
        if (! $user->hasRole('Member')) {
            $rules['member_id'] = ['required', 'exists:members,id'];
        }

        $validated = $request->validate($rules, [
            'trainer_id.required' => 'Silakan pilih trainer yang diinginkan.',
            'session_date.required' => 'Tanggal sesi latihan wajib diisi.',
            'session_date.after_or_equal' => 'Tanggal sesi latihan tidak boleh di masa lampau.',
            'training_focus.required' => 'Fokus/target latihan wajib diisi.',
            'member_id.required' => 'Silakan pilih data member.',
        ]);

        $memberId = $user->hasRole('Member') ? $user->member?->id : $validated['member_id'];

        if (! $memberId) {
            return back()->with('error', 'Profil member tidak ditemukan.');
        }

        $booking = TrainerBooking::create([
            'trainer_id' => $validated['trainer_id'],
            'member_id' => $memberId,
            'time_slot_id' => $validated['time_slot_id'] ?? null,
            'session_date' => $validated['session_date'],
            'training_focus' => $validated['training_focus'],
            'notes' => $validated['notes'] ?? null,
            'status' => TrainerBooking::STATUS_PENDING,
        ]);

        $trainerName = $booking->trainer?->user?->name ?? 'Trainer';

        return redirect()->route('trainer-bookings.index')
            ->with('success', "Permohonan sesi latihan bersama {$trainerName} berhasil diajukan dan sedang menunggu validasi.");
    }

    /**
     * Setujui (ACC) permohonan sesi latihan oleh Trainer atau Admin.
     */
    public function approve(Request $request, TrainerBooking $trainerBooking): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Validasi otoritas: Trainer hanya boleh ACC booking untuk dirinya sendiri
        if ($user->hasRole('Trainer') && $trainerBooking->trainer_id !== $user->trainer?->id) {
            abort(403, 'Anda tidak memiliki akses untuk menyetujui sesi trainer lain.');
        }

        // Simpan nomor telepon member jika diinput dan sebelumnya belum ada
        if ($request->filled('member_phone') && $trainerBooking->member) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->member_phone);
            if (! empty($cleanPhone) && empty($trainerBooking->member->phone)) {
                $trainerBooking->member->update(['phone' => $cleanPhone]);
                $trainerBooking->load('member.user');
            }
        }

        $trainerBooking->update([
            'status' => TrainerBooking::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $memberName = $trainerBooking->member?->user?->name ?? 'Member';
        $waUrl = $trainerBooking->whatsapp_url;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sesi latihan untuk member {$memberName} berhasil disetujui (ACC).",
                'whatsapp_url' => $waUrl,
                'whatsapp_message' => $trainerBooking->getWhatsAppConfirmationMessage(),
            ]);
        }

        $destination = $request->filled('redirect_to') ? $request->redirect_to : route('trainer-bookings.index');
        $redirect = redirect($destination)
            ->with('success', "Sesi latihan untuk member {$memberName} berhasil disetujui (ACC).");

        if ($request->boolean('open_wa') && $waUrl) {
            $redirect->with('whatsapp_open_url', $waUrl);
        }

        return $redirect;
    }

    /**
     * Tolak permohonan sesi latihan oleh Trainer atau Admin.
     */
    public function reject(Request $request, TrainerBooking $trainerBooking): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Validasi otoritas: Trainer hanya boleh Tolak booking untuk dirinya sendiri
        if ($user->hasRole('Trainer') && $trainerBooking->trainer_id !== $user->trainer?->id) {
            abort(403, 'Anda tidak memiliki akses untuk menolak sesi trainer lain.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan sesi latihan wajib diisi.',
            'reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        // Simpan nomor telepon member jika diinput dan sebelumnya belum ada
        if ($request->filled('member_phone') && $trainerBooking->member) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->member_phone);
            if (! empty($cleanPhone) && empty($trainerBooking->member->phone)) {
                $trainerBooking->member->update(['phone' => $cleanPhone]);
                $trainerBooking->load('member.user');
            }
        }

        $trainerBooking->update([
            'status' => TrainerBooking::STATUS_REJECTED,
            'rejection_reason' => $validated['reason'],
        ]);

        $memberName = $trainerBooking->member?->user?->name ?? 'Member';
        $waRejectUrl = $trainerBooking->getWhatsAppRejectionUrl($validated['reason']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Permohonan sesi latihan dari {$memberName} telah ditolak.",
                'whatsapp_url' => $waRejectUrl,
                'whatsapp_message' => $trainerBooking->getWhatsAppRejectionMessage($validated['reason']),
            ]);
        }

        $destination = $request->filled('redirect_to') ? $request->redirect_to : route('trainer-bookings.index');
        $redirect = redirect($destination)
            ->with('success', "Permohonan sesi latihan dari {$memberName} telah ditolak.");

        if ($request->boolean('open_wa') && $waRejectUrl) {
            $redirect->with('whatsapp_open_url', $waRejectUrl);
        }

        return $redirect;
    }

    /**
     * Tandai sesi latihan telah selesai dilaksanakan.
     */
    public function complete(Request $request, TrainerBooking $trainerBooking): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if ($user->hasRole('Trainer') && $trainerBooking->trainer_id !== $user->trainer?->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah status sesi trainer lain.');
        }

        $trainerBooking->update([
            'status' => TrainerBooking::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $memberName = $trainerBooking->member?->user?->name ?? 'Member';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sesi latihan dengan member {$memberName} telah selesai dilaksanakan.",
            ]);
        }

        $destination = $request->filled('redirect_to') ? $request->redirect_to : route('trainer-bookings.index');

        return redirect($destination)
            ->with('success', "Sesi latihan dengan member {$memberName} telah selesai dilaksanakan.");
    }
}
