<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

trait EnrollmentReminderShared
{
    public function sharedReminderQuery(Carbon $end, Carbon $start)
    {
        $from = $start->toDateTimeString();
        $to   = $end->toDateTimeString();
//         We send the first notification marked as is_reminder => false
//         We send the second notification(reminder => true).
//         We dont want to send a second reminder if user has 1 true and 1 false is_reminder.
        return User::whereHas('notifications', function ($notification) use ($to, $from) {
            $notification
                ->where('data->is_reminder', false)
                ->where([
                    ['created_at', '>=', $from],
                    ['created_at', '<=', $to],
                ])
                ->where(function ($q) {
                    $q->where('type', SendEnrollmentEmail::class)->orWhere('type', SendEnrollementSms::class);
                });
        })
            // Enrollees also have User and Patient_info this point
            ->whereHas('patientInfo', function ($patient) use ($from, $to) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })
            ->whereDoesntHave('notifications', function ($notification) use ($to, $from) {
                $notification
                    ->where('data->is_reminder', true)
                    ->where([
                        ['created_at', '>=', $from],
                        ['created_at', '<=', $to],
                    ])
                    ->where(function ($q) {
                        $q->where('type', SendEnrollmentEmail::class)->orWhere('type', SendEnrollementSms::class);
                    });
            });
    }
}
