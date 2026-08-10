<?php

use App\Models\User;
use Carbon\Carbon;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response
        ->assertOk()
        ->assertSee('Create program')
        ->assertSee('size-10 sm:size-9', escape: false)
        ->assertDontSee('overflow-x-auto', escape: false);
});

test('the dashboard shows this months workouts and links to their entries', function () {
    $this->travelTo(Carbon::parse('2026-08-08 12:00:00'));

    $user = User::factory()->create(['timezone' => 'UTC']);
    $program = $user->programs()->create(['name' => 'Strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);
    $entry = $user->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => '2026-08-05',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Create program')
        ->assertSee('August 2026')
        ->assertSee('data-workout-date="2026-08-05"', escape: false)
        ->assertSee(route('workout-entries.edit', $entry, absolute: false))
        ->assertDontSee('Start with your plan')
        ->assertDontSee('Your history stays yours')
        ->assertDontSee('kg or lbs');
});

test('the dashboard does not show another users workouts', function () {
    $this->travelTo(Carbon::parse('2026-08-08 12:00:00'));

    $user = User::factory()->create(['timezone' => 'UTC']);
    $otherUser = User::factory()->create(['timezone' => 'UTC']);
    $program = $otherUser->programs()->create(['name' => 'Private strength']);
    $workout = $program->workouts()->create(['label' => 'A', 'position' => 0]);
    $entry = $otherUser->workoutEntries()->create([
        'workout_id' => $workout->id,
        'performed_on' => '2026-08-05',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-workout-date="2026-08-05"', escape: false)
        ->assertDontSee(route('workout-entries.edit', $entry, absolute: false));
});
