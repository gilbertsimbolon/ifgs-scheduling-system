<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code')->nullable()->unique()->after('slug')->comment('Kode ID unik resmi pengguna IFGS');
        });

        // Generate unique user_code for all existing users
        $users = User::with('member')->orderBy('id')->get();
        $prefix = 'IFGS-'.now()->format('Ym').'-';
        $seq = 1;

        foreach ($users as $user) {
            if ($user->member && ! empty($user->member->member_code)) {
                $code = $user->member->member_code;
            } else {
                do {
                    $code = $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                    $seq++;
                } while (User::where('user_code', $code)->exists() || Member::where('member_code', $code)->exists());
            }

            $user->updateQuietly(['user_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_code');
        });
    }
};
