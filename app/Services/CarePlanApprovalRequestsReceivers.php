<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;

class CarePlanApprovalRequestsReceivers
{
    /**
     * Returns who should receive care plan approval requests for a provider's patients according to the forwarding rules, if any are set.
     */
    public static function forProvider(User $providerUser): Collection
    {
        $recipients = collect();

        if ($providerUser->forwardAlertsTo->isEmpty()) {
            $recipients->push($providerUser);
        } else {
            foreach ($providerUser->forwardAlertsTo as $forwardee) {
                if (User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER == $forwardee->pivot->name) {
                    $recipients->push($providerUser);
                    $recipients->push($forwardee);
                }

                if (User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER == $forwardee->pivot->name) {
                    $recipients->push($forwardee);
                }
            }
        }

        return $recipients;
    }
}
