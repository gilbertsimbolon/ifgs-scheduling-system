<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MembershipTransactionController extends Controller
{
    //
    /**
     * Menampilkan daftar transaksi membership untuk validasi kasir/admin.
     */
    public function index(Request $request): View
    {
        $query = Membership::with(['member.user', 'product', 'paymentMethod', 'transaction.user'])->latest();

        // Filter status
        if ($request->filled('status') && in_array($request->status, Membership::STATUSES)) {
            $query->where('status', $request->status);
        }

        // Filter metode pembayaran
        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('member.user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($tq) use ($search) {
                        $tq->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $memberships = $query->paginate(15)->withQueryString();

        // Metrics untuk ringkasan validasi
        $metrics = [
            'pending' => Membership::where('status', Membership::STATUS_PENDING)->count(),
            'approved' => Membership::where('status', Membership::STATUS_ACTIVE)->count(),
            'rejected' => Membership::whereIn('status', [Membership::STATUS_REJECTED, Membership::STATUS_CANCELLED])->count(),
            'total_revenue' => Membership::where('status', Membership::STATUS_ACTIVE)->sum('price'),
        ];

        $paymentMethods = PaymentMethod::orderBy('name')->get();
        $statuses = Membership::STATUSES;

        return view('membership_transaction.index', compact(
            'memberships',
            'metrics',
            'paymentMethods',
            'statuses'
        ));
    }

    /**
     * Menyetujui (ACC) transaksi pembayaran membership oleh kasir/admin.
     */
    public function approve(Request $request, Membership $membership): RedirectResponse|JsonResponse
    {
        $startDate = $membership->start_date ? $membership->start_date->format('Y-m-d') : now()->toDateString();
        if (Carbon::parse($startDate)->isPast() && ! Carbon::parse($startDate)->isToday()) {
            $startDate = now()->toDateString();
        }
        $endDate = $membership->product->calculateEndDate($startDate)->format('Y-m-d');

        DB::transaction(function () use ($membership, $startDate, $endDate) {
            if ($membership->transaction) {
                $membership->transaction->update([
                    'status' => Transaction::STATUS_COMPLETED,
                    'user_id' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }

            $membership->update([
                'status' => Membership::STATUS_ACTIVE,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        });

        $memberName = $membership->member?->user?->name ?? 'Member';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi membership untuk {$memberName} berhasil disetujui (ACC) dan diaktifkan.",
            ]);
        }

        return redirect()->route('membership-transactions.index')
            ->with('success', "Transaksi membership untuk {$memberName} berhasil disetujui (ACC) dan diaktifkan.");
    }

    /**
     * Menolak transaksi pembayaran membership oleh kasir/admin.
     */
    public function reject(Request $request, Membership $membership): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        DB::transaction(function () use ($membership, $validated) {
            if ($membership->transaction) {
                $membership->transaction->update([
                    'status' => Transaction::STATUS_REJECTED,
                    'user_id' => auth()->id(),
                    'rejection_reason' => $validated['reason'],
                ]);
            }

            $membership->update([
                'status' => Membership::STATUS_REJECTED,
            ]);
        });

        $memberName = $membership->member?->user?->name ?? 'Member';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Transaksi membership untuk {$memberName} telah ditolak.",
            ]);
        }

        return redirect()->route('membership-transactions.index')
            ->with('success', "Transaksi membership untuk {$memberName} telah ditolak.");
    }
}
