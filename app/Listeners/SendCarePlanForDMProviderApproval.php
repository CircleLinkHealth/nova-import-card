<?php

namespace App\Listeners;

use App\Events\CarePlanWasQAApproved;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use App\Services\CarePlanApprovalRequestsReceivers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCarePlanForDMProviderApproval
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        if (CarePlan::PROVIDER_APPROVED === $event->patient->carePlan->status) {
            return;
        }
    
        $notification = new SendCarePlanForDirectMailApprovalNotification($event->patient);
        
        if ($billingProvider = $event->patient->billingProviderUser())
        CarePlanApprovalRequestsReceivers::forProvider($billingProvider)->each(function (User $provider) use ($notification) {
            $provider->notify($notification);
        });
    }
}
