<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use App\CareAmbassador;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeCallQueue
{
    static function getNext(CareAmbassador $careAmbassador)
    {

        $queue = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
            ? \Cache::get("care_ambassador_{$careAmbassador->id}_queue")
            : [];

        //add more logic to this
        //if previous enrollee id, try to get call_queue or maybe engaged family enrollees
        if ( ! empty($queue)) {
            $nextEnrolleeId = collect($queue)->first();
            $enrollee       = Enrollee::find($nextEnrolleeId);
        }

        if ( ! isset($enrollee) || is_null($enrollee)) {
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

            $engagedEnrollee = Enrollee::where('care_ambassador_user_id', $careAmbassador->user_id)
                                       ->where('status', '=', Enrollee::ENGAGED)
                                       ->orderBy('attempt_count')
                                       ->with(['practice.enrollmentTips', 'provider.providerInfo'])
                                       ->first();

            if ($engagedEnrollee) {
                $enrollee = $engagedEnrollee;
            }
        }

        if ($enrollee) {
            //mark as engaged to prevent double dipping
            $enrollee->status = Enrollee::ENGAGED;
            //re-assign care ambassador, in case patient has been retrieved as a confirmed family member
            $enrollee->care_ambassador_user_id = $careAmbassador->user_id;
            $enrollee->save();
        }

        return $enrollee;
    }

    static function update(CareAmbassador $careAmbassador, Enrollee $enrollee, $confirmedFamilyMembers)
    {
        $queue = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
            ? \Cache::get("care_ambassador_{$careAmbassador->id}_queue")
            : [];

        $queue = collect(array_merge($queue,
            explode(',', $confirmedFamilyMembers)))->filter()->unique()->toArray();

        if ( ! empty($queue) && in_array($enrollee->id, $queue)) {
            unset($queue[array_search($enrollee->id, $queue)]);
        }

        \Cache::put("care_ambassador_{$careAmbassador->id}_queue", $queue, 10);
    }
}