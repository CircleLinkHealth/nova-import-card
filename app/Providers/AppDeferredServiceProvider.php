<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\ReportFormatter;
use App\Formatters\WebixFormatter;
use CircleLinkHealth\Core\Services\SnappyPdfWrapper;
use CircleLinkHealth\Core\Services\HtmlToPdfService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class AppDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            DevelopmentServiceProvider::class,
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
            ReportFormatter::class,
            WebixFormatter::class
        );
    }
}
