<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Observers\OutgoingSmsObserver;
use CircleLinkHealth\Core\Console\Commands\Vapor\DeleteAllSecrets;
use CircleLinkHealth\Core\Console\Commands\Vapor\UploadSecretsFromFile;
use CircleLinkHealth\SharedModels\Entities\OutgoingSms;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        OutgoingSms::observe(OutgoingSmsObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->commands([
                DeleteAllSecrets::class,
                UploadSecretsFromFile::class,
            ]);
        }
    }
}
