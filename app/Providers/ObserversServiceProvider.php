<?php

namespace App\Providers;

use App\NurseContactWindow;
use App\Observers\NurseContactWindowObserver;
use Illuminate\Support\ServiceProvider;

class ObserversServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        NurseContactWindow::observe(NurseContactWindowObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
