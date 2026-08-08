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
        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->string('weight_mode')->default('total')->after('weight_unit');
            $table->decimal('bar_weight', 8, 2)->nullable()->after('weight_mode');
            $table->json('plate_counts')->nullable()->after('bar_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->dropColumn(['weight_mode', 'bar_weight', 'plate_counts']);
        });
    }
};
