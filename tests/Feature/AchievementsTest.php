<?php

use App\Models\User;
use Livewire\Livewire;

test('the achievements page renders configured workout-count thumbnails', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test('pages::achievements.index')
        ->assertOk()
        ->assertSee(asset('images/achievements/workout-count/001-thumbnail.png'), escape: false)
        ->assertSee(asset('images/achievements/workout-count/003-thumbnail.png'), escape: false)
        ->assertSee(asset('images/achievements/workout-count/005-thumbnail.png'), escape: false)
        ->assertSee(asset('images/achievements/workout-count/010-thumbnail.png'), escape: false)
        ->assertSee(asset('images/achievements/workout-count/020-thumbnail.png'), escape: false)
        ->assertSee('grid-cols-[repeat(auto-fill,4.6875rem)]', escape: false)
        ->assertSee('sm:grid-cols-[repeat(auto-fill,6.25rem)]', escape: false)
        ->assertSee('size-[75px]', escape: false)
        ->assertSee('sm:size-[100px]', escape: false)
        ->assertSee('Twenty Strong');
});
