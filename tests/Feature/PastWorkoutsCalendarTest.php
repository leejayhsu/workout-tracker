<?php

use Illuminate\Support\Facades\Blade;

test('uses a distinct hand-drawn marker for every possible day of the month', function () {
    $calendar = Blade::render('<x-past-workouts-calendar static multiple :value="[\'2026-08-01\']" />');

    expect($calendar)
        ->toContain('data-past-workouts-calendar')
        ->toContain('data-past-workout-marker')
        ->toContain('past-workouts-marker-roughness');

    foreach (range(1, 31) as $day) {
        expect($calendar)->toContain(sprintf("data-date$='-%02d'", $day));
    }
});
