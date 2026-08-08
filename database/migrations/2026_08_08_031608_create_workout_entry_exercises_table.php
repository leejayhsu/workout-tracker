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
        Schema::create('workout_entry_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_entry_id')->constrained()->cascadeOnDelete();
            $table->string('exercise_key')->nullable();
            $table->string('exercise_name');
            $table->unsignedTinyInteger('position');
            $table->unsignedSmallInteger('sets')->default(0);
            $table->unsignedSmallInteger('reps')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit', 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_entry_exercises');
    }
};
