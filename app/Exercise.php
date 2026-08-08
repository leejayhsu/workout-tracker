<?php

namespace App;

enum Exercise: string
{
    case BarbellBackSquat = 'barbell_back_squat';
    case BarbellFrontSquat = 'barbell_front_squat';
    case BarbellBenchPress = 'barbell_bench_press';
    case CloseGripBenchPress = 'close_grip_bench_press';
    case FloorPress = 'floor_press';
    case BarbellRow = 'barbell_row';
    case PendlayRow = 'pendlay_row';
    case Deadlift = 'deadlift';
    case RomanianDeadlift = 'romanian_deadlift';
    case BarbellGoodMorning = 'barbell_good_morning';
    case BarbellHipThrust = 'barbell_hip_thrust';
    case BarbellSplitSquat = 'barbell_split_squat';
    case BarbellShrug = 'barbell_shrug';
    case OverheadPress = 'overhead_press';
    case BarbellCurl = 'barbell_curl';
    case BarbellReverseCurl = 'barbell_reverse_curl';
    case LyingBarbellTricepsExtension = 'lying_barbell_triceps_extension';
    case OverheadBarbellTricepsExtension = 'overhead_barbell_triceps_extension';
    case Dip = 'dip';
    case PullUp = 'pull_up';
    case ChinUp = 'chin_up';
    case PushUp = 'push_up';
    case CloseGripPushUp = 'close_grip_push_up';
    case DeclinePushUp = 'decline_push_up';
    case BodyweightSquat = 'bodyweight_squat';
    case BulgarianSplitSquat = 'bulgarian_split_squat';
    case BodyweightReverseLunge = 'bodyweight_reverse_lunge';

    public function label(): string
    {
        return match ($this) {
            self::BarbellBackSquat => 'Barbell back squat',
            self::BarbellFrontSquat => 'Barbell front squat',
            self::BarbellBenchPress => 'Barbell bench press',
            self::CloseGripBenchPress => 'Close-grip bench press',
            self::FloorPress => 'Floor press',
            self::BarbellRow => 'Barbell row',
            self::PendlayRow => 'Pendlay row',
            self::Deadlift => 'Deadlift',
            self::RomanianDeadlift => 'Romanian deadlift',
            self::BarbellGoodMorning => 'Barbell good morning',
            self::BarbellHipThrust => 'Barbell hip thrust',
            self::BarbellSplitSquat => 'Barbell split squat',
            self::BarbellShrug => 'Barbell shrug',
            self::OverheadPress => 'Overhead press',
            self::BarbellCurl => 'Barbell curl',
            self::BarbellReverseCurl => 'Barbell reverse curl',
            self::LyingBarbellTricepsExtension => 'Lying barbell triceps extension',
            self::OverheadBarbellTricepsExtension => 'Overhead barbell triceps extension',
            self::Dip => 'Dip',
            self::PullUp => 'Pull-up',
            self::ChinUp => 'Chin-up',
            self::PushUp => 'Push-up',
            self::CloseGripPushUp => 'Close-grip push-up',
            self::DeclinePushUp => 'Decline push-up',
            self::BodyweightSquat => 'Bodyweight squat',
            self::BulgarianSplitSquat => 'Bulgarian split squat',
            self::BodyweightReverseLunge => 'Bodyweight reverse lunge',
        };
    }
}
