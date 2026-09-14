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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_code', 30)->unique()->comment('Nomor unik jadwal kunjungan, e.g. SCH-20260911-0001');
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->restrictOnDelete();
            $table->date('scheduled_date')->comment('Tanggal kunjungan yang dijadwalkan');
            $table->string('status', 20)->default('scheduled')->comment('scheduled, attended, cancelled, no_show');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['scheduled_date', 'time_slot_id', 'status']);
            $table->index(['member_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
