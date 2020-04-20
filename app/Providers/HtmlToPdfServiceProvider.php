<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use CircleLinkHealth\Core\HtmlToPdfService;
use App\Services\SnappyPdfWrapper;
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
