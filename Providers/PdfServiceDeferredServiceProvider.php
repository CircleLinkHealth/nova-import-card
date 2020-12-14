<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\PdfService\Providers;

use CircleLinkHealth\PdfService\Services\HtmlToPdfService;
use CircleLinkHealth\PdfService\Services\ServerlessPdfService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class PdfServiceDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            HtmlToPdfService::class,
        ];
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind(HtmlToPdfService::class, function () {
            return new ServerlessPdfService();
            /*
                $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);

                return $this->app->make(SnappyPdfWrapper::class)
                    ->setTemporaryFolder(storage_path('tmp'))
                    ->handler();
                */
        });
    }
}
