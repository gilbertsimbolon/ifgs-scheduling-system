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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('trainer_code')->unique()->comment('Kode unik trainer, misal: TRN-001');
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable()->comment('Bidang spesialisasi, misal: Fitness & Bodybuilding, Aerobic & Zumba');
            $table->text('bio')->nullable()->comment('Profil singkat atau sertifikasi');
            $table->string('status')->default('active')->comment('active atau inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
