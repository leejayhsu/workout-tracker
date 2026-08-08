<?php

use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('guests cannot view programs', function () {
    $this->get(route('programs.index'))->assertRedirect(route('login'));
});

test('a user can create a program with one to seven repeating workouts', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('pages::programs.create')
        ->set('name', 'StrongLifts 5x5')
        ->set('workouts', ['Squat, bench press, row', 'Squat, deadlift, overhead press', '', '', '', '', ''])
        ->call('createProgram')
        ->assertRedirect(route('programs.index', absolute: false));

    $program = Program::query()->where('user_id', $user->id)->firstOrFail();

    expect($program->name)->toBe('StrongLifts 5x5')
        ->and($program->workouts)->toHaveCount(2)
        ->and($program->workouts->pluck('label')->all())->toBe(['A', 'B']);
});

test('a program requires at least one workout', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('pages::programs.create')
        ->set('name', 'Empty plan')
        ->call('createProgram')
        ->assertHasErrors('workouts');

    expect(Program::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('users only see their own programs', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherUser->programs()->create(['name' => 'Private plan']);

    Livewire::actingAs($user)->test('pages::programs.index')
        ->assertOk()
        ->assertDontSee('Private plan');
});

test('a user can navigate from a program to its workouts', function () {
    $user = User::factory()->create();
    $program = $user->programs()->create(['name' => 'Strength']);
    $program->workouts()->create(['label' => 'A', 'position' => 0]);

    Livewire::actingAs($user)->test('pages::programs.index')
        ->assertSee(route('workouts.index', $program, absolute: false));
});

test('a program shows its workout sequence without a workout count', function () {
    $user = User::factory()->create();
    $program = $user->programs()->create(['name' => 'Strength']);
    $program->workouts()->createMany([
        ['label' => 'A', 'position' => 0],
        ['label' => 'B', 'position' => 1],
    ]);

    Livewire::actingAs($user)->test('pages::programs.index')
        ->assertSee('A / B')
        ->assertDontSee('2 workouts');
});

test('a program shows recent workout activity', function () {
    $this->travelTo(Carbon::parse('2026-08-07 12:00:00'));

    $user = User::factory()->create(['timezone' => 'UTC']);
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);
    $entry = $user->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => '2026-08-07',
    ]);

    Livewire::actingAs($user)->test('pages::programs.index')
        ->assertSee('aria-label="Recent workout activity"', escape: false)
        ->assertSee('data-activity-date="2026-08-07"', escape: false)
        ->assertSee(route('workout-entries.edit', $entry, absolute: false))
        ->assertDontSee('data-activity-date="2026-07-10"', escape: false);
});

test('a user can rename a program and its workouts without leaving the workouts page', function () {
    $user = User::factory()->create();
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'name' => 'Full body', 'position' => 0]);

    Livewire::actingAs($user)->test('pages::workouts.index', ['program' => $program])
        ->assertSee('data-flux-tabs')
        ->assertSee('data-flux-tab')
        ->assertSee('data-flux-tab-panel')
        ->call('editProgram')
        ->set('programName', 'Summer strength')
        ->call('saveProgram')
        ->assertSet('editingProgram', false)
        ->call('editWorkout', $workout->id)
        ->set('workoutName', 'Upper body')
        ->call('saveWorkout')
        ->assertSet('editingWorkoutId', null)
        ->assertSee('Summer strength')
        ->assertSee('Upper body');

    expect($program->fresh()->name)->toBe('Summer strength')
        ->and($workout->fresh()->name)->toBe('Upper body');
});

test('a user can create a workout entry from the latest previous entry', function () {
    $user = User::factory()->create(['timezone' => 'America/New_York']);
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'name' => 'Full body', 'position' => 0]);
    $previousEntry = $user->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => now($user->timezone)->toDateString(),
    ]);
    $previousEntry->exercises()->create([
        'exercise_key' => 'deadlift',
        'exercise_name' => 'Deadlift',
        'position' => 0,
        'weight_unit' => 'kg',
    ]);
    $previousEntry->exercises->first()->sets()->createMany([
        ['position' => 0, 'reps' => 5, 'weight' => 100],
        ['position' => 1, 'reps' => 5, 'weight' => 100],
        ['position' => 2, 'reps' => 4, 'weight' => 105],
    ]);

    Livewire::actingAs($user)->test('pages::workout-entries.create', ['workout' => $workout])
        ->assertSee('data-flux-date-picker')
        ->assertSet('exercises.0.exercise_name', 'Deadlift')
        ->assertSet('exercises.0.sets.0.weight', '100')
        ->assertSet('exercises.0.sets.2.reps', 4)
        ->call('addSet', 0)
        ->assertSet('exercises.0.sets.3.reps', 4)
        ->assertSet('exercises.0.sets.3.weight', '105')
        ->call('createEntry')
        ->assertRedirect();

    $entry = $workout->entries()->latest('id')->firstOrFail();

    expect($entry->performed_on->toDateString())->toBe(now($user->timezone)->toDateString())
        ->and($entry->exercises)->toHaveCount(1)
        ->and($entry->exercises->first()->sets)->toHaveCount(4)
        ->and($entry->exercises->first()->sets->last()->reps)->toBe(4)
        ->and($entry->exercises->first()->sets->last()->weight)->toBe('105.00');

    Livewire::actingAs($user)->test('pages::workout-entries.edit', ['workoutEntry' => $entry])
        ->assertSee('data-flux-date-picker');
});

test('a user can add exercises with the searchable picker', function () {
    $user = User::factory()->create();
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);

    Livewire::actingAs($user)->test('pages::workout-entries.create', ['workout' => $workout])
        ->set('exerciseToAdd', 'deadlift')
        ->assertSet('exerciseToAdd', null)
        ->assertSet('exercises.0.exercise_key', 'deadlift')
        ->assertSet('exercises.0.exercise_name', 'Deadlift')
        ->set('exerciseToAdd', 'deadlift')
        ->assertCount('exercises', 2)
        ->assertSee('Search exercises...')
        ->assertDontSee('>Unit<', escape: false)
        ->assertSee('h-8 text-sm rounded-md', escape: false);
});

test('a user can record barbell weight as standard plates per side', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);

    Livewire::actingAs($user)->test('pages::workout-entries.create', ['workout' => $workout])
        ->set('exerciseToAdd', 'barbell_bench_press')
        ->set('exercises.0.weight_unit', 'lbs')
        ->call('addSet', 0)
        ->set('exercises.0.sets.0.reps', 5)
        ->set('exercises.0.sets.0.weight', 95)
        ->call('openPlateCalculator', 0, 0)
        ->assertSee('class="grid grid-cols-2 gap-4"', escape: false)
        ->assertSet('plateCalculatorBarWeight', '45')
        ->assertSet('plateCalculatorCounts.25', 1)
        ->call('incrementPlate', '45')
        ->call('incrementPlate', '25')
        ->call('decrementPlate', '25')
        ->assertSet('plateCalculatorCounts.45', 1)
        ->assertSet('plateCalculatorCounts.25', 1)
        ->set('plateCalculatorCounts', ['45' => 1, '25' => 1, '5' => 1])
        ->call('applyPlateWeight')
        ->assertSet('exercises.0.sets.0.weight', '195')
        ->call('addSet', 0)
        ->set('exercises.0.sets.1.reps', 4)
        ->call('createEntry')
        ->assertRedirect();

    $exercise = $workout->entries()->latest('id')->firstOrFail()->exercises->firstOrFail();

    expect($exercise->sets)->toHaveCount(2)
        ->and($exercise->sets[0]->weight)->toBe('195.00')
        ->and($exercise->sets[1]->weight)->toBe('195.00');
});

test('a user can record different reps and weight for each set', function () {
    $user = User::factory()->create();
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);

    Livewire::actingAs($user)->test('pages::workout-entries.create', ['workout' => $workout])
        ->set('exerciseToAdd', 'deadlift')
        ->call('addSet', 0)
        ->set('exercises.0.sets.0.reps', 5)
        ->set('exercises.0.sets.0.weight', 100)
        ->call('addSet', 0)
        ->set('exercises.0.sets.1.reps', 3)
        ->set('exercises.0.sets.1.weight', 110)
        ->call('createEntry');

    $sets = $workout->entries()->latest('id')->firstOrFail()->exercises->firstOrFail()->sets;

    expect($sets->map(fn ($set): array => [$set->reps, $set->weight])->all())
        ->toBe([[5, '100.00'], [3, '110.00']]);
});

test('a user can adjust reps and weight with the mobile number editor', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);

    $component = Livewire::actingAs($user)->test('pages::workout-entries.create', ['workout' => $workout])
        ->set('exerciseToAdd', 'deadlift')
        ->call('addSet', 0)
        ->set('exercises.0.sets.0.weight', '100.00');

    $component->call('openMobileNumberEditor', 0, 0, 'reps')
        ->assertSet('mobileEditorValue', '0')
        ->call('incrementMobileNumber')
        ->call('incrementMobileNumber')
        ->call('applyMobileNumber')
        ->assertSet('exercises.0.sets.0.reps', 2)
        ->call('openMobileNumberEditor', 0, 0, 'weight')
        ->call('incrementMobileNumber')
        ->call('applyMobileNumber')
        ->assertSet('exercises.0.sets.0.weight', '101');
});

test('users cannot access another users workout entries', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $program = $owner->programs()->create(['name' => 'Private']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);
    $entry = $owner->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => now()->toDateString(),
    ]);

    Livewire::actingAs($otherUser)->test('pages::workout-entries.edit', ['workoutEntry' => $entry])
        ->assertStatus(404);
});
