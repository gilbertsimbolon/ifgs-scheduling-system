<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->unique()->after('slug')->comment('Kode QR unik absensi member/user');
        });

        // Generate unique qr_code for any existing users
        $users = User::whereNull('qr_code')->get();
        foreach ($users as $user) {
            do {
                $code = 'IFGS-QR-'.strtoupper(Str::random(10));
            } while (User::where('qr_code', $code)->exists());

            $user->update(['qr_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('qr_code');
        });
    }
};
