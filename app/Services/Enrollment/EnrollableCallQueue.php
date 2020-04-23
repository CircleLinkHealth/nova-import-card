<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;

use App\CareAmbassador;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrollableCallQueue
{
    public static function getNext(CareAmbassador $careAmbassador)
    {
        $queue = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
            ? \Cache::get("care_ambassador_{$careAmbassador->id}_queue")
            : [];

        //add more logic to this
        //do not check status for call_queue. If they have been selected by the CA they must have been on call queue initially
        //per CPM-2256 we will be applying the same statuses on confirmed family members, so that we can pre-fill their data on the page.
        if ( ! empty($queue)) {
            $nextEnrolleeId = collect($queue)->first();
            //maybe add specific
            $enrollee = Enrollee::find($nextEnrolleeId);
        }

        if ( ! isset($enrollee) || is_null($enrollee)) {
            //get all careAmbassador enrollees / do not prioritize speaks spanish

            //get all CA enrollees.

            //TOP PRIO - patients in queue (confirmed family members)

            //1st prio - Confirmed family members whom statuses have not been confirmed - edge case - add UI

            //2nd prio - utc patients where attempt count 1 && last attempt > 3 days ago

            //3nd prio - >> attempt count 2

            //4th prio - call queue

            //Post conditions - never bring enrolled, consented, soft or hard decline, utc x3, ineligible, legacy

            //if logged in ambassador is spanish, pick up a spanish patient
            if ($careAmbassador->speaks_spanish) {
                $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                    ->toCall()
                    ->where('lang', 'ES')
                    ->orderBy('attempt_count')
                    ->with(['practice.enrollmentTips', 'provider.providerInfo'])
                    ->first();

                //if no spanish, get a EN user.
                if (null == $enrollee) {
                    $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                        ->toCall()
                        ->orderBy('attempt_count')
                        ->with(['practice.enrollmentTips', 'provider.providerInfo'])
                        ->first();
                }
            } else { // auth ambassador doesn't speak ES, get a regular user.
                $enrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                    ->toCall()
                    ->orderBy('attempt_count')
                    ->with(['practice.enrollmentTips', 'provider.providerInfo'])
                    ->first();
            }
        }

        if ($enrollee) {
            //re-assign care ambassador, in case patient has been retrieved as a confirmed family member
            $enrollee->care_ambassador_user_id = $careAmbassador->user_id;
            $enrollee->save();
        }

        return $enrollee;
    }

    public static function update(CareAmbassador $careAmbassador, Enrollee $enrollee, $confirmedFamilyMembers)
    {
        $queue = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
            ? \Cache::get("care_ambassador_{$careAmbassador->id}_queue")
            : [];

        $queue = collect(array_merge(
            $queue,
            explode(',', $confirmedFamilyMembers)
        ))->filter()->unique()->toArray();

        if ( ! empty($queue) && in_array($enrollee->id, $queue)) {
            unset($queue[array_search($enrollee->id, $queue)]);
        }

        \Cache::put("care_ambassador_{$careAmbassador->id}_queue", $queue, 600);
    }
}
