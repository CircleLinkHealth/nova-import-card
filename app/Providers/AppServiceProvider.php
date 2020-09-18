<?php

namespace App\Providers;

use App\Observers\OutgoingSmsObserver;
use CircleLinkHealth\SharedModels\Entities\OutgoingSms;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        OutgoingSms::observe(OutgoingSmsObserver::class);
    }
}
