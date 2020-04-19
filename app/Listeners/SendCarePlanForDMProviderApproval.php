<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\AppConfig\DMDomainForAutoApproval;
use App\Events\CarePlanWasQAApproved;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use App\Services\CarePlanApprovalRequestsReceivers;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCarePlanForDMProviderApproval implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(CarePlanWasQAApproved $event)
    {
        if ($this->shouldBail($event)) {
            return;
        }

        $notification = new SendCarePlanForDirectMailApprovalNotification($event->patient);

        if ($billingProvider = $event->patient->billingProviderUser()) {
            CarePlanApprovalRequestsReceivers::forProvider($billingProvider)->each(
                function (User $provider) use ($notification) {
                    $provider->notify($notification);
                }
            );
        }
    }

    private function shouldBail($event): bool
    {
        if (CarePlan::QA_APPROVED !== $event->patient->carePlan->status) {
            return true;
        }

        if ( ! DMDomainForAutoApproval::isEnabledForPractice($event->patient->program_id)) {
            return true;
        }

        return false;
    }
}
