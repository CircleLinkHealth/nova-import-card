<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Composer;

class Scripts
{
    public function postDeploy()
    {
        if ( ! isProductionEnv()) {
            return;
        }
        \Artisan::call('migrate', [
            '--force' => true,
        ]);

        \Artisan::call('deploy:post');
    }
}
