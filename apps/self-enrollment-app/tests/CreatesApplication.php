<?php

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
        $basePath = $_SERVER['OLDPWD'];
        $app = require "$basePath/bootstrap/app.php";

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
