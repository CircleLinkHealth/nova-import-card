<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Notifications\SendEnrollmentEmail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

trait EnrollmentReminderShared
{
    public function sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
    {
        return User::whereHas('notifications', function ($notification) use ($untilEndOfDay, $twoDaysAgo) {
            $notification
                ->where('data->is_reminder', false)
                ->where([
                    ['created_at', '>=', $twoDaysAgo],
                    ['created_at', '<=', $untilEndOfDay],
                ])->where('type', SendEnrollmentEmail::class);
        })
            // Enrollees also have User and Patient_info this point
            ->whereHas('patientInfo', function ($patient) use ($twoDaysAgo, $untilEndOfDay) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            });
    }
}
