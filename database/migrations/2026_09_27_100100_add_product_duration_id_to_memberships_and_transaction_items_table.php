<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('memberships', 'product_duration_id')) {
                $table->foreignId('product_duration_id')->nullable()->after('product_id')->constrained('product_durations')->nullOnDelete();
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (! Schema::hasColumn('transaction_items', 'product_duration_id')) {
                $table->foreignId('product_duration_id')->nullable()->after('product_id')->constrained('product_durations')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (Schema::hasColumn('memberships', 'product_duration_id')) {
                $table->dropForeign(['product_duration_id']);
                $table->dropColumn('product_duration_id');
            }
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_items', 'product_duration_id')) {
                $table->dropForeign(['product_duration_id']);
                $table->dropColumn('product_duration_id');
            }
        });
    }
};
