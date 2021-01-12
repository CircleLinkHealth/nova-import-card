<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Condition logic
    |--------------------------------------------------------------------------
    |
    | The custom condition logic you want to execute to generate (or not) the
    | Preload script. You can use any class using it's name, or Class@method
    | notation. This will be executed using the Service Container's "call".
    |
    */

    'condition' => \DarkGhostHunter\Laraload\Conditions\CountRequests::class,

    /*
    |--------------------------------------------------------------------------
    | Output
    |--------------------------------------------------------------------------
    |
    | Once the Preload script is generated, it will written to the storage
    | path of your application, since it should have permission to write.
    | You can change the script output for anything as long is writable.
    |
    */

    'output' => storage_path('preload.php'),

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | The Preloader script can be configured to handle a limited number of
    | files based on their memory consumption. The default is a safe bet
    | for most apps, but you can change it for your app specifically.
    |
    */

    'memory' => 64,

    /*
    |--------------------------------------------------------------------------
    | Upload method
    |--------------------------------------------------------------------------
    |
    | Opcache supports preloading files by using `require_once` (which executes
    | and resolves each file link), and `opcache_compile_file` (which not). If
    | you want to use require ensure the Composer Autoloader path is correct.
    |
    */

    'use_require' => false,
    'autoload'    => base_path('vendor/autoload.php'),

    /*
    |--------------------------------------------------------------------------
    | Ignore Not Found
    |--------------------------------------------------------------------------
    |
    | Sometimes Opcache will include in the list files that are generated by
    | Laravel at runtime which don't exist when deploying the application.
    | To avoid errors on preloads, we can tell Preloader to ignore them.
    |
    */

    'ignore-not-found' => true,
];
