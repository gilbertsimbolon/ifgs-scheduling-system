<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['price', 'duration_value', 'duration_unit'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('products', 'duration_value')) {
                $table->unsignedInteger('duration_value')->nullable();
            }
            if (! Schema::hasColumn('products', 'duration_unit')) {
                $table->string('duration_unit')->nullable();
            }
        });
    }
};
