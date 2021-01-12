<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\AppConfig\DMDomainForAutoApproval;
use App\Notifications\SendCarePlanForDirectMailApprovalNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Events\CarePlanWasRNApproved;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use CircleLinkHealth\SharedModels\Services\CarePlanApprovalRequestsReceivers;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCarePlanForDMProviderApproval implements ShouldQueue, ShouldBeEncrypted
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
    public function handle(CarePlanWasRNApproved $event)
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

    private function isDMApprovalEnabled($patient)
    {
        if (DMDomainForAutoApproval::isEnabledForPractice($patient->program_id)) {
            return true;
        }
    }

    private function shouldBail($event): bool
    {
        if (CarePlan::RN_APPROVED !== $event->patient->carePlan->status) {
            return true;
        }

        if ( ! $this->isDMApprovalEnabled($event->patient)) {
            return true;
        }

        return false;
    }
}
