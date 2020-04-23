<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Services\SnappyPdfWrapper;
use CircleLinkHealth\Core\HtmlToPdfService;
use Illuminate\Support\ServiceProvider;

class HtmlToPdfServiceProvider extends ServiceProvider
{
    protected $defer = true;

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
