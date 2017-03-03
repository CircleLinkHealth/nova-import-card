<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        if (env('APP_URL')) {
            $this->baseUrl = env('APP_URL');
        }

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

}
