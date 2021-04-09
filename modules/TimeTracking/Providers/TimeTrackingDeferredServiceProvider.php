<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Providers;

use CircleLinkHealth\TimeTracking\Console\Commands\RecalculateCcmTime;
use CircleLinkHealth\TimeTracking\Console\Commands\RemoveTimeFromNurseCareRateLogs;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TimeTrackingDeferredServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            RecalculateCcmTime::class,
            RemoveTimeFromNurseCareRateLogs::class,
        ];
    }

    public function register()
    {
        $this->commands([
            RecalculateCcmTime::class,
            RemoveTimeFromNurseCareRateLogs::class,
        ]);
    }
}
