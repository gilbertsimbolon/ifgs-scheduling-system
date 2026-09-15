<?php

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
        Schema::table('time_slots', function (Blueprint $table) {
            $table->string('category', 50)->default('fitness')->after('name')->comment('Kategori layanan: fitness, aerobic_zumba');
            $table->string('days', 100)->default('Senin - Sabtu')->after('category')->comment('Hari operasional sesi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn(['category', 'days']);
        });
    }
};
