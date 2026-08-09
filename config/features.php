<?php

return [
    'achievements' => env('ACHIEVEMENTS_ENABLED', env('APP_ENV', 'production') !== 'production'),
];
