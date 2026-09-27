<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_durations')) {
            Schema::create('product_durations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedInteger('duration_value');
                $table->string('duration_unit');
                $table->decimal('price', 12, 2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['product_id', 'duration_value', 'duration_unit']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_durations');
    }
};
