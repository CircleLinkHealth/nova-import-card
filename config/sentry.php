<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN', null),

    // capture release as git sha
    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
];
