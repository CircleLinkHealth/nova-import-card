<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class AlgoTestController extends Controller
{
    public function algoCleaner()
    {
        if (isProductionEnv()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \CircleLinkHealth\SharedModels\Services\SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();
    }

    public function algoFamily()
    {
        if (isProductionEnv()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \CircleLinkHealth\SharedModels\Services\SchedulerService())->syncFamilialCalls();
    }

    public function algoRescheduler()
    {
        if (isProductionEnv()) {
            return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Algorithms\Calls\ReschedulerHandler())->handle();
    }

    public function algoTuner()
    {
        if (isProductionEnv()) {
            return 'Sorry, this cannot be run on the production environment.';
        }
    }
}
