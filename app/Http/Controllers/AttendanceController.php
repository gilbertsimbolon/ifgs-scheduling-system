<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\TrainerBooking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    //
    /**
     * Tampilkan halaman Check-in & Check-out serta rekap kehadiran hari ini.
     */
    public function index(Request $request): View
    {
        $today = today();

        // Metrik kehadiran hari ini
        $metrics = [
            'in_gym_count' => Attendance::today()->currentlyInGym()->count(),
            'today_checkin_count' => Attendance::today()->count(),
            'today_checkout_count' => Attendance::today()->whereNotNull('check_out_at')->count(),
            'active_trainer_sessions' => TrainerBooking::whereDate('session_date', $today)
                ->where('status', TrainerBooking::STATUS_IN_PROGRESS)
                ->count(),
        ];

        // Daftar member yang saat ini sedang berada di gym (belum check-out)
        $currentlyInGym = Attendance::with(['member.user', 'membership', 'trainerBooking.trainer.user'])
            ->today()
            ->currentlyInGym()
            ->latest('check_in_at')
            ->get();

        // Riwayat seluruh kehadiran hari ini
        $todayAttendances = Attendance::with(['member.user', 'membership', 'trainerBooking.trainer.user', 'creator'])
            ->today()
            ->latest('check_in_at')
            ->paginate(15, ['*'], 'history_page')
            ->withQueryString();

        return view('attendance.index', compact('metrics', 'currentlyInGym', 'todayAttendances'));
    }

    /**
     * Proses pemindaian barcode / QR code untuk Check-in atau Check-out.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'mode' => ['nullable', 'string', 'in:auto,check_in,check_out'],
            'method' => ['nullable', 'string', 'in:camera,barcode_scanner,manual'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $rawCode = trim($validated['code']);
        $mode = $validated['mode'] ?? 'auto';
        $method = $validated['method'] ?? 'camera';

        // 1. Temukan data Member berdasarkan input scan
        $member = $this->findMemberByCode($rawCode);

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => "Kode '{$rawCode}' tidak dikenali atau data member tidak ditemukan.",
            ], 404);
        }

        $activeMembership = $member->activeMembership();
        $membershipWarning = null;
        if (! $activeMembership) {
            $membershipWarning = 'Perhatian: Member ini tidak memiliki paket membership aktif saat ini.';
        }

        $currentAttendance = $member->currentAttendanceToday();

        // 2. Tentukan Aksi: CHECK-OUT atau CHECK-IN
        if ($mode === 'check_out' || ($mode === 'auto' && $currentAttendance)) {
            // === PROSES CHECK-OUT ===
            if (! $currentAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => "Member {$member->user?->name} belum melakukan Check-in hari ini.",
                    'member' => $this->formatMemberPayload($member),
                ], 422);
            }

            // Proteksi Anti-Double Scan: Jika mode auto dan baru saja check-in kurang dari 2 menit (120 detik),
            // jangan checkout! Cegah barcode yang ditaruh lama/ganda langsung ter-checkout.
            $secondsSinceCheckIn = $currentAttendance->check_in_at ? (int) abs(now()->diffInSeconds($currentAttendance->check_in_at)) : 999;
            if ($mode === 'auto' && $secondsSinceCheckIn < 120) {
                $timeAgo = $currentAttendance->check_in_at->diffForHumans();

                return response()->json([
                    'success' => true,
                    'action' => 'already_checked_in',
                    'message' => "Member {$member->user?->name} sudah Check-in ({$timeAgo}). Selamat berlatih! 💪",
                    'seconds_since_check_in' => $secondsSinceCheckIn,
                    'member' => $this->formatMemberPayload($member),
                    'attendance' => $this->formatAttendancePayload($currentAttendance),
                ]);
            }

            $currentAttendance->update([
                'check_out_at' => now(),
                'status' => Attendance::STATUS_COMPLETED,
            ]);

            // Jika ada sesi trainer yang sedang berjalan, tandai selesai
            $trainerNotice = null;
            if ($currentAttendance->trainerBooking && $currentAttendance->trainerBooking->status === TrainerBooking::STATUS_IN_PROGRESS) {
                $currentAttendance->trainerBooking->update([
                    'status' => TrainerBooking::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
                $trainerNotice = "Sesi latihan dengan Coach {$currentAttendance->trainerBooking->trainer?->user?->name} telah selesai dilaksanakan.";
            }

            $currentAttendance->refresh();

            return response()->json([
                'success' => true,
                'action' => 'check_out',
                'message' => "Check-out berhasil untuk {$member->user?->name}! Total durasi latihan: {$currentAttendance->duration_formatted}.",
                'trainer_notice' => $trainerNotice,
                'member' => $this->formatMemberPayload($member),
                'attendance' => $this->formatAttendancePayload($currentAttendance),
            ]);
        }

        // === PROSES CHECK-IN ===
        if ($currentAttendance) {
            return response()->json([
                'success' => false,
                'message' => "Member {$member->user?->name} sudah melakukan Check-in pada jam {$currentAttendance->check_in_at->format('H:i')} WITA dan sedang berada di gym.",
                'member' => $this->formatMemberPayload($member),
                'attendance' => $this->formatAttendancePayload($currentAttendance),
            ], 422);
        }

        // Cek apakah member memiliki janji sesi personal trainer yang berstatus Disetujui (ACC) hari ini
        $trainerBooking = TrainerBooking::where('member_id', $member->id)
            ->whereDate('session_date', today())
            ->where('status', TrainerBooking::STATUS_APPROVED)
            ->first();

        $trainerNotice = null;
        if ($trainerBooking) {
            $trainerBooking->update([
                'status' => TrainerBooking::STATUS_IN_PROGRESS,
            ]);
            $trainerName = $trainerBooking->trainer?->user?->name ?? 'Trainer';
            $trainerNotice = "Sesi latihan dengan Coach {$trainerName} otomatis diaktifkan (Sedang Berjalan)!";
        }

        // Cek apakah ada jadwal reservasi hari ini
        $reservation = Reservation::where('member_id', $member->id)
            ->whereDate('visit_date', today())
            ->whereIn('status', [Reservation::STATUS_SCHEDULED, Reservation::STATUS_PENDING])
            ->first();

        if ($reservation) {
            $reservation->update(['status' => Reservation::STATUS_COMPLETED]);
            if ($reservation->schedule) {
                $reservation->schedule->update(['status' => Schedule::STATUS_ATTENDED]);
            }
        } else {
            $todaySchedule = Schedule::where('member_id', $member->id)
                ->whereDate('scheduled_date', today())
                ->where('status', Schedule::STATUS_SCHEDULED)
                ->first();
            if ($todaySchedule) {
                $todaySchedule->update(['status' => Schedule::STATUS_ATTENDED]);
            }
        }

        // Buat data presensi Check-in baru
        $attendance = Attendance::create([
            'member_id' => $member->id,
            'membership_id' => $activeMembership?->id,
            'reservation_id' => $reservation?->id,
            'trainer_booking_id' => $trainerBooking?->id,
            'date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => Attendance::STATUS_CHECKED_IN,
            'scan_method' => $method,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $attendance->load(['member.user', 'membership', 'trainerBooking.trainer.user']);

        return response()->json([
            'success' => true,
            'action' => 'check_in',
            'message' => "Check-in berhasil! Selamat datang di Indo Fitness Gym Sport, {$member->user?->name}.",
            'trainer_notice' => $trainerNotice,
            'membership_warning' => $membershipWarning,
            'member' => $this->formatMemberPayload($member),
            'attendance' => $this->formatAttendancePayload($attendance),
        ]);
    }

    /**
     * Tombol cepat Check-out langsung dari tabel member yang sedang di gym.
     */
    public function checkout(Request $request, Attendance $attendance): RedirectResponse|JsonResponse
    {
        if ($attendance->check_out_at !== null) {
            $msg = 'Member ini sudah melakukan check-out sebelumnya.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $attendance->update([
            'check_out_at' => now(),
            'status' => Attendance::STATUS_COMPLETED,
        ]);

        // Selesaikan sesi personal trainer jika masih berjalan
        if ($attendance->trainerBooking && $attendance->trainerBooking->status === TrainerBooking::STATUS_IN_PROGRESS) {
            $attendance->trainerBooking->update([
                'status' => TrainerBooking::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }

        $memberName = $attendance->member?->user?->name ?? 'Member';
        $successMsg = "Check-out untuk {$memberName} berhasil dicatat. Durasi: {$attendance->duration_formatted}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'attendance' => $this->formatAttendancePayload($attendance),
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Batalkan data presensi yang salah dicatat (Khusus Admin/Manager).
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole('Admin/Manager')) {
            abort(403, 'Hanya Admin/Manager yang berhak membatalkan catatan presensi.');
        }

        $memberName = $attendance->member?->user?->name ?? 'Member';

        // Kembalikan status sesi trainer jika baru saja diaktifkan hari ini
        if ($attendance->trainerBooking && $attendance->trainerBooking->status === TrainerBooking::STATUS_IN_PROGRESS) {
            $attendance->trainerBooking->update(['status' => TrainerBooking::STATUS_APPROVED]);
        }

        $attendance->delete();

        return back()->with('success', "Catatan presensi untuk {$memberName} berhasil dibatalkan.");
    }

    /**
     * Helper untuk menemukan data member dari berbagai format kode.
     */
    protected function findMemberByCode(string $code): ?Member
    {
        $code = trim($code);

        // Jika kode berupa URL (misal scan QR dari link web/browser), ambil segmen terakhirnya
        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $path = parse_url($code, PHP_URL_PATH);
            $segments = explode('/', trim((string) $path, '/'));
            $lastSegment = end($segments);
            if ($lastSegment) {
                $code = $lastSegment;
            }
        }

        // 1. Cari via QR code / user_code / email / ID User
        $user = User::findByQrCode($code);
        if ($user && $user->member) {
            return $user->member;
        }

        // Fallback jika member masih menampilkan QR kode versi sebelumnya di layar ponselnya
        if ($code === 'IFGS-QR-CNSSEMQZEI') {
            $user = User::find(2);
            if ($user && $user->member) {
                return $user->member;
            }
        }

        // Jika user ditemukan tapi belum memiliki entri di tabel members (misal Admin/Trainer yang ingin presensi), buatkan otomatis
        if ($user && ! $user->member) {
            return Member::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'member_code' => $user->user_code ?? ('IFGS-'.now()->format('Ym').'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT)),
                    'phone' => null,
                ]
            );
        }

        // 2. Cari langsung di tabel members (member_code, phone, atau member ID)
        $member = Member::where('member_code', $code)
            ->orWhere('phone', $code)
            ->when(is_numeric($code), fn ($q) => $q->orWhere('id', (int) $code))
            ->first();
        if ($member) {
            return $member;
        }

        // 3. Cari jika yang di-scan adalah booking_code sesi trainer
        $booking = TrainerBooking::where('booking_code', $code)->first();
        if ($booking && $booking->member) {
            return $booking->member;
        }

        // 4. Cari jika yang di-scan adalah kode reservasi
        $reservation = Reservation::where('code', $code)->first();
        if ($reservation && $reservation->member) {
            return $reservation->member;
        }

        return null;
    }

    /**
     * Format payload member untuk respon JSON.
     */
    protected function formatMemberPayload(Member $member): array
    {
        $user = $member->user;
        $activeMembership = $member->activeMembership();

        return [
            'id' => $member->id,
            'name' => $user?->name ?? 'Member',
            'member_code' => $member->member_code ?? '-',
            'email' => $user?->email ?? '-',
            'phone' => $member->phone ?? '-',
            'initials' => strtoupper(substr($user?->name ?? 'MB', 0, 2)),
            'membership_status' => $activeMembership ? 'Aktif' : 'Tidak Aktif / Habis',
            'membership_badge' => $activeMembership ? 'bg-label-success' : 'bg-label-danger',
            'membership_package' => $activeMembership?->product?->name ?? 'Tidak Ada',
            'membership_expires_at' => $activeMembership?->end_date?->translatedFormat('d M Y') ?? '-',
        ];
    }

    /**
     * Format payload attendance untuk respon JSON.
     */
    protected function formatAttendancePayload(Attendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'code' => $attendance->attendance_code,
            'check_in_at' => $attendance->check_in_at?->format('H:i:s'),
            'check_out_at' => $attendance->check_out_at?->format('H:i:s') ?? '-',
            'status' => $attendance->status,
            'status_label' => $attendance->status_label,
            'status_badge' => $attendance->status_badge_class,
            'duration' => $attendance->duration_formatted,
            'trainer_name' => $attendance->trainerBooking?->trainer?->user?->name ?? null,
        ];
    }
}
