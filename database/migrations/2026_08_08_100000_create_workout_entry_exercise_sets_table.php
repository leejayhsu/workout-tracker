<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_entry_exercise_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_entry_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('reps')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['workout_entry_exercise_id', 'position']);
        });

        DB::table('workout_entry_exercises')->orderBy('id')->each(function (object $exercise): void {
            $sets = [];

            for ($position = 0; $position < $exercise->sets; $position++) {
                $sets[] = [
                    'workout_entry_exercise_id' => $exercise->id,
                    'position' => $position,
                    'reps' => $exercise->reps,
                    'weight' => $exercise->weight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($sets !== []) {
                DB::table('workout_entry_exercise_sets')->insert($sets);
            }
        });

        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->dropColumn(['sets', 'reps', 'weight']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_entry_exercise_sets');

        Schema::table('workout_entry_exercises', function (Blueprint $table) {
            $table->unsignedSmallInteger('sets')->default(0);
            $table->unsignedSmallInteger('reps')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
        });
    }
};
