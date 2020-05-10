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
//            If still unreachable means user did not choose to "Enroll Now" in invitation mail.
//                This Check may be unnecessary in the case of enrollees. But is double check. @todo:confirm!
            ->whereHas('patientInfo', function ($patient) use ($twoDaysAgo, $untilEndOfDay) {
                $patient->where('ccm_status', Patient::UNREACHABLE)->where([
                    ['date_unreachable', '>=', $twoDaysAgo],
                    ['date_unreachable', '<=', $untilEndOfDay],
                ]);
            });
    }
}
