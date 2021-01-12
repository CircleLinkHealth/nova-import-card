<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if ( ! getenv('CI')) {
            $app->loadEnvironmentFrom('.env.testing');
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
