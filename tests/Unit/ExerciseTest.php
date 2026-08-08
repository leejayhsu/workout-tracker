<?php

use App\Exercise;

test('the exercise catalog includes rack, barbell, bench, and dip-bar exercises', function () {
    expect(Exercise::cases())->toHaveCount(27)
        ->and(Exercise::BarbellFrontSquat->label())->toBe('Barbell front squat')
        ->and(Exercise::RomanianDeadlift->label())->toBe('Romanian deadlift')
        ->and(Exercise::CloseGripBenchPress->label())->toBe('Close-grip bench press')
        ->and(Exercise::BarbellCurl->label())->toBe('Barbell curl')
        ->and(Exercise::LyingBarbellTricepsExtension->label())->toBe('Lying barbell triceps extension')
        ->and(Exercise::OverheadBarbellTricepsExtension->label())->toBe('Overhead barbell triceps extension')
        ->and(Exercise::Dip->label())->toBe('Dip')
        ->and(Exercise::PullUp->label())->toBe('Pull-up')
        ->and(Exercise::ChinUp->label())->toBe('Chin-up')
        ->and(Exercise::PushUp->label())->toBe('Push-up')
        ->and(Exercise::CloseGripPushUp->label())->toBe('Close-grip push-up')
        ->and(Exercise::DeclinePushUp->label())->toBe('Decline push-up')
        ->and(Exercise::BodyweightSquat->label())->toBe('Bodyweight squat')
        ->and(Exercise::BulgarianSplitSquat->label())->toBe('Bulgarian split squat')
        ->and(Exercise::BodyweightReverseLunge->label())->toBe('Bodyweight reverse lunge');
});
