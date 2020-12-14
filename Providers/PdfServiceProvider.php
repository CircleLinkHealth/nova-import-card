<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Providers;

use CircleLinkHealth\PdfService\Commands\TestServerlessPdfService;
use Illuminate\Support\ServiceProvider;

class PdfServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);

        $this->commands([
            TestServerlessPdfService::class,
        ]);
    }
}
