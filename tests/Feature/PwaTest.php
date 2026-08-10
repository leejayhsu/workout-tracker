<?php

use App\Models\User;

test('the authenticated app shell includes PWA metadata', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('<meta name="apple-mobile-web-app-capable" content="yes">', escape: false)
        ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', escape: false)
        ->assertDontSee('favicon.ico', escape: false)
        ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', escape: false);
});

test('the PWA manifest defines the app scope and icons', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest)
        ->start_url->toBe('/dashboard')
        ->scope->toBe('/')
        ->display->toBe('standalone')
        ->icons->toHaveCount(2);
});
