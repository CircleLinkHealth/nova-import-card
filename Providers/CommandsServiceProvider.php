<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Providers;

use CircleLinkHealth\CpmAdmin\Console\Commands\CountBillablePatientsForMonth;
use CircleLinkHealth\CpmAdmin\Console\Commands\CountPatientMonthlySummaryCalls;
use CircleLinkHealth\CpmAdmin\Console\Commands\SyncNumberOfCallsForCurrentMonth;
use CircleLinkHealth\SelfEnrollment\Console\Commands\ManuallyCreateEnrollmentTestData;
use Illuminate\Support\ServiceProvider;

class CommandsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            CountBillablePatientsForMonth::class,
            CountPatientMonthlySummaryCalls::class,
            ManuallyCreateEnrollmentTestData::class,
            SyncNumberOfCallsForCurrentMonth::class,
        ]);
    }
}
