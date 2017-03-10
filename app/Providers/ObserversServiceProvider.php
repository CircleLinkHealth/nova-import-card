<?php

namespace App\Providers;

use App\CarePlan;
use App\NurseContactWindow;
use App\Observers\CarePlanObserver;
use App\Observers\NurseContactWindowObserver;
use App\Observers\PageTimerObserver;
use App\Observers\UserObserver;
use App\PageTimer;
use App\User;
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
        CarePlan::observe(CarePlanObserver::class);
        NurseContactWindow::observe(NurseContactWindowObserver::class);
        PageTimer::observe(PageTimerObserver::class);
        User::observe(UserObserver::class);
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
