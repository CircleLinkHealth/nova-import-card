<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp()
    {
        parent::setUp();

        Artisan::call('migrate:refresh');

        Artisan::call('db:seed');
        Artisan::call('db:seed', [
            '--class' => 'SnomedToIcd9TestMapTableSeeder'
        ]);
        Artisan::call('db:seed', [
            '--class' => 'TestSuiteSeeder'
        ]);
//        Artisan::call('lada:flush');

        \Log::useFiles('php://stderr');
        \Log::useFiles('php://stdout');
    }
}
