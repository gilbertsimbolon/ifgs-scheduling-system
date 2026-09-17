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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('attendance_code', 30)->unique()->comment('ATT-YYYYMMDD-XXXX');
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained('memberships')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('trainer_booking_id')->nullable()->constrained('trainer_bookings')->nullOnDelete();
            $table->date('date')->comment('Tanggal kunjungan gym');
            $table->timestamp('check_in_at')->comment('Waktu check-in masuk');
            $table->timestamp('check_out_at')->nullable()->comment('Waktu check-out keluar');
            $table->string('status', 20)->default('checked_in')->comment('checked_in, completed, cancelled');
            $table->string('scan_method', 30)->default('camera')->comment('camera, barcode_scanner, manual');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date', 'status']);
            $table->index(['member_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
