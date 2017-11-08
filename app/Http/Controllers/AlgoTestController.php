<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AlgoTestController extends Controller
{
	
    public function algoFamily()
    {
    	if (app()->environment() == 'production')
    	{
           return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->syncFamilialCalls();
    }



    public function algoCleaner()
    {
    	if (app()->environment() == 'production') 
    	{
           return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->removeScheduledCallsForWithdrawnAndPausedPatients();
    }


    public function algoTuner()
    {
		if (app()->environment() == 'production') {
           return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Services\Calls\SchedulerService())->tuneScheduledCallsWithUpdatedCCMTime();

    }


    public function algoRescheduler()
    {
    	if (app()->environment() == 'production') {
           return 'Sorry, this cannot be run on the production environment.';
        }

        return (new \App\Algorithms\Calls\ReschedulerHandler())->handle();
    }
}
