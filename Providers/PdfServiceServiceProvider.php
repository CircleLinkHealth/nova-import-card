<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Pdfservice\Providers;

use App\Console\Commands\TestServerlessPdfService;
use Illuminate\Support\ServiceProvider;

class PdfServiceServiceProvider extends ServiceProvider
{
    public function register()
    {
        //todo: register SnappyServiceProvider
        $this->commands([
            TestServerlessPdfService::class,
        ]);
    }
}
