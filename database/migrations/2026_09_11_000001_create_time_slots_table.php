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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nama sesi / slot, misal: Sesi Pagi 1');
            $table->string('start_time', 10)->comment('Jam mulai kunjungan format HH:mm, misal: 08:00');
            $table->string('end_time', 10)->comment('Jam selesai kunjungan format HH:mm, misal: 09:00');
            $table->unsignedSmallInteger('capacity')->default(10)->comment('Kapasitas maksimum member per slot');
            $table->string('status', 20)->default('active')->comment('Status slot: active, inactive');
            $table->timestamps();

            $table->index(['status', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
