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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('type')->default('cash')->after('name');
            $table->string('account_number')->nullable()->after('code');
            $table->string('account_name')->nullable()->after('account_number');
            $table->string('qr_image')->nullable()->after('account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'account_number', 'account_name', 'qr_image']);
        });
    }
};
