<?php

namespace App\Listeners;

use App\Events\CarePlanWasQAApproved;
use App\Events\PdfableCreated;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AutoApproveCarePlan implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * Handle the event.
     *
     * @param CarePlanWasQAApproved $event
     *
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        if ( ! $this->shouldAutoApprove($event)) {
            return;
        }
        
        $event->patient->carePlan->status               = CarePlan::PROVIDER_APPROVED;
        $event->patient->carePlan->provider_approver_id = optional($event->patient->billingProviderUser())->id;
        $event->patient->carePlan->save();
    }
    
    private function shouldAutoApprove(CarePlanWasQAApproved $event): bool
    {
        return (bool) $event->patient->primaryPractice->cpmSettings()->auto_approve_careplans;
    }
}
