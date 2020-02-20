<?php

namespace App\Listeners;

use App\Events\CarePlanWasProviderApproved;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForwardApprovedCarePlanToPractice implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CarePlanWasProviderApproved $event)
    {
        $event->patient->carePlan->forward();
    }
}
