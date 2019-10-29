<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\HtmlToPdfService;
use App\Contracts\ReportFormatter;
use App\Formatters\WebixFormatter;
use App\Services\SnappyPdfWrapper;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AppDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            DevelopmentServiceProvider::class,
            HtmlToPdfService::class,
            ReportFormatter::class,
        ];
    }

    /**
     * Register services.
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            DevelopmentServiceProvider::class;
        }

        $this->app->bind(
            HtmlToPdfService::class,
            function () {
                $this->app->register(\Barryvdh\Snappy\ServiceProvider::class);

                return $this->app->make(SnappyPdfWrapper::class)
                    ->setTemporaryFolder(storage_path('tmp'));
            }
        );

        $this->app->bind(
            ReportFormatter::class,
            WebixFormatter::class
        );
    }
}
