<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        // 1. Buat profil Member untuk semua user yang belum memiliki record member (termasuk Admin & Trainer yang ingin presensi)
        $usersWithoutMember = User::doesntHave('member')->get();
        foreach ($usersWithoutMember as $user) {
            Member::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'member_code' => $user->user_code ?? ('IFGS-'.now()->format('Ym').'-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT)),
                    'phone' => null,
                ]
            );
        }

        // 2. Selaraskan qr_code agar sama persis dengan ID Member / user_code
        // Sehingga tidak ada lagi perbedaan antara ID yang tertera di kartu dengan isi barcode/QR
        $allUsers = User::with('member')->get();
        foreach ($allUsers as $user) {
            $officialId = $user->member?->member_code ?? $user->user_code;
            if (! empty($officialId)) {
                $user->qr_code = $officialId;
                $user->saveQuietly();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        // No reverse needed
    }
};
