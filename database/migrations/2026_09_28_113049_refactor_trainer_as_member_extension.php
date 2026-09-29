<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom pendukung data trainer ke tabel members
        Schema::table('members', function (Blueprint $table) {
            if (! Schema::hasColumn('members', 'is_trainer')) {
                $table->boolean('is_trainer')->default(false)->after('phone');
            }
            if (! Schema::hasColumn('members', 'specialization')) {
                $table->string('specialization')->nullable()->after('is_trainer');
            }
            if (! Schema::hasColumn('members', 'bio')) {
                $table->text('bio')->nullable()->after('specialization');
            }
            if (! Schema::hasColumn('members', 'trainer_status')) {
                $table->string('trainer_status', 20)->default('active')->after('bio');
            }
        });

        // 2. Migrasikan data trainers lama (jika ada) ke tabel members
        if (Schema::hasTable('trainers')) {
            $oldTrainers = DB::table('trainers')->get();
            foreach ($oldTrainers as $old) {
                if ($old->user_id) {
                    $existingMember = DB::table('members')->where('user_id', $old->user_id)->first();
                    if ($existingMember) {
                        DB::table('members')->where('id', $existingMember->id)->update([
                            'is_trainer' => true,
                            'specialization' => $old->specialization ?? null,
                            'bio' => $old->bio ?? null,
                            'phone' => $existingMember->phone ?: ($old->phone ?? null),
                            'trainer_status' => $old->status ?? 'active',
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('members')->insert([
                            'user_id' => $old->user_id,
                            'member_code' => 'IFGS-M-TRN'.str_pad((string) $old->id, 3, '0', STR_PAD_LEFT),
                            'phone' => $old->phone ?? null,
                            'is_trainer' => true,
                            'specialization' => $old->specialization ?? null,
                            'bio' => $old->bio ?? null,
                            'trainer_status' => $old->status ?? 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // 3. Lepas dependensi attendances terhadap trainer_booking_id
        if (Schema::hasTable('attendances') && Schema::hasColumn('attendances', 'trainer_booking_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                try {
                    $table->dropForeign(['trainer_booking_id']);
                } catch (Throwable $e) {
                    // Foreign key might not exist or have a different name in sqlite
                }
                $table->dropColumn('trainer_booking_id');
            });
        }

        // 4. Drop tabel-tabel scheduling/booking personal trainer yang sudah di luar scope skripsi
        Schema::dropIfExists('trainer_bookings');
        Schema::dropIfExists('trainer_clients');
        Schema::dropIfExists('trainers');

        // 5. Hapus role Trainer dan permission terkait dari Spatie jika ada
        try {
            Role::where('name', 'Trainer')->delete();
            Permission::whereIn('name', ['manage-trainer-bookings', 'manage-own-trainer-bookings'])->delete();
        } catch (Throwable $e) {
            // Abaikan jika tabel roles belum ada atau error di environment tertentu
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'trainer_status')) {
                $table->dropColumn('trainer_status');
            }
            if (Schema::hasColumn('members', 'bio')) {
                $table->dropColumn('bio');
            }
            if (Schema::hasColumn('members', 'specialization')) {
                $table->dropColumn('specialization');
            }
            if (Schema::hasColumn('members', 'is_trainer')) {
                $table->dropColumn('is_trainer');
            }
        });
    }
};
