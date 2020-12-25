<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Finder\Finder;

$files = Finder::create()->in([
    __DIR__.'/app',
    __DIR__.'/bootstrap',
    __DIR__.'/routes',
    __DIR__.'/storage/framework/views',
    __DIR__.'/vendor/composer',
    __DIR__.'/vendor/laravel/framework',
    __DIR__.'/CircleLinkHealth',
])
    ->name('*.php')
    ->ignoreUnreadableDirs()
    ->notContains('#!/usr/bin/env php')
    ->exclude([
        [
            'Autoload',
            'test',
            'Test',
            'tests',
            'Tests',
            'stub',
            'Stub',
            'stubs',
            'Stubs',
            'dumper',
            'Dumper',
        ],
    ])
    ->files()
    ->followLinks();

foreach ($files as $file) {
    try {
        if ( ! (is_file($file) && is_readable($file))) {
            continue;
        }
        opcache_compile_file($file);
    } catch (\Throwable $e) {
        echo 'Preloader Script has stopped with an error:'.\PHP_EOL;
        echo 'Message: '.$e->getMessage().\PHP_EOL;
        echo 'File: '.$file.\PHP_EOL;

        throw $e;
    }
}
