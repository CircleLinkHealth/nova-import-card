<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class AlgoTestController extends Controller
{
    public function algoCleaner()
    {
        if ('production' == app()->environment()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();
    }

    public function algoFamily()
    {
        if ('production' == app()->environment()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->syncFamilialCalls();
    }

    public function algoRescheduler()
    {
        if ('production' == app()->environment()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Algorithms\Calls\ReschedulerHandler())->handle();
    }

    public function algoTuner()
    {
        if ('production' == app()->environment()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->tuneScheduledCallsWithUpdatedCCMTime();
    }
}
