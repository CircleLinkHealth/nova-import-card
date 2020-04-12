<?php

namespace App\Listeners;

use App\Events\CarePlanWasApproved;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCcdaStatus implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CarePlanWasApproved $event)
    {
        Ccda::where('patient_id', $event->patient->id)->update([
            'status' => Ccda::CAREPLAN_CREATED,
                                                               ]);
    }
}
