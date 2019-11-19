<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Composer;

class Scripts
{
    public static function postDeploy()
    {
        if ( ! in_array(getenv('APP_ENV'), ['local', 'testing'])) {
            return;
        }
        \Artisan::call('migrate', [
            '--force' => true,
        ]);

        \Artisan::call('deploy:post');
    }
}
