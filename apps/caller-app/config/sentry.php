<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', null),

    // capture release as git sha
    'release' => trim(exec('date +"%Y-%m-%d_%H-%M-%S"')),
];
