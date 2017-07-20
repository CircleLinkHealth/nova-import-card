<?php

namespace App\Providers;

use App\CarePlan;
use App\CarePlanTemplate;
use App\Models\Holiday;
use App\NurseContactWindow;
use App\Observers\CarePlanObserver;
use App\Observers\CarePlanTemplateObserver;
use App\Observers\HolidayObserver;
use App\Observers\NurseContactWindowObserver;
use App\Observers\PageTimerObserver;
use App\Observers\PatientObserver;
use App\Observers\UserObserver;
use App\PageTimer;
use App\Patient;
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
        CarePlanTemplate::observe(CarePlanTemplateObserver::class);
        NurseContactWindow::observe(NurseContactWindowObserver::class);
        Holiday::observe(HolidayObserver::class);
        PageTimer::observe(PageTimerObserver::class);
        Patient::observe(PatientObserver::class);
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
