<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->dropColumn(['weight_mode', 'bar_weight', 'plate_counts']);
        });
    }

    public function down(): void
    {
        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->string('weight_mode')->default('total');
            $table->decimal('bar_weight', 8, 2)->nullable();
            $table->json('plate_counts')->nullable();
        });
    }
};
