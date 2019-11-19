<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Composer;

class Scripts
{
    public static function postDeploy()
    {
        $env = getenv('APP_ENV');

        if ( ! in_array($env, ['local', 'testing'])) {
            echo "Not running because env is $env";

            return;
        }
        \Artisan::call('migrate', [
            '--force' => true,
        ]);

        \Artisan::call('deploy:post');
    }
}
