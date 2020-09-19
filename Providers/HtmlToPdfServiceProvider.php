<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Providers;

use CircleLinkHealth\Core\Services\SnappyPdfWrapper;
use CircleLinkHealth\Core\Services\HtmlToPdfService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class HtmlToPdfServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            HtmlToPdfService::class,
        ];
    }

    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(
            HtmlToPdfService::class,
            function () {
                return $this->app->make(SnappyPdfWrapper::class)
                    ->setTemporaryFolder(storage_path('tmp'));
            }
        );
    }
}
