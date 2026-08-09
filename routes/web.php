<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('programs', 'pages::programs.index')->name('programs.index');
    Route::livewire('programs/create', 'pages::programs.create')->name('programs.create');
    Route::livewire('programs/{program}/workouts', 'pages::workouts.index')->name('workouts.index');
    Route::livewire('workouts/{workout}/entries/create', 'pages::workout-entries.create')->name('workout-entries.create');
    Route::livewire('workout-entries/{workoutEntry}/edit', 'pages::workout-entries.edit')->name('workout-entries.edit');
    Route::post('timezone', function (Request $request) {
        $validated = $request->validate(['timezone' => ['required', 'timezone']]);
        $request->user()->update(['timezone' => $validated['timezone']]);

        return response()->json(['updated' => true]);
    })->name('timezone.update');
});

require __DIR__.'/settings.php';
