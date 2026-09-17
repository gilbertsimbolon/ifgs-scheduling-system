<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = User::whereNull('qr_code')->get();
        foreach ($users as $user) {
            $user->qr_code = User::generateUniqueQrCode();
            $user->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
