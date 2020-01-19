<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Enrollment;


use CircleLinkHealth\Eligibility\Entities\Enrollee;

class EnrolleeCallQueue
{
    static function getNext(){

        $careAmbassador = auth()->user()->careAmbassador;

        $queue          = \Cache::has("care_ambassador_{$careAmbassador->id}_queue")
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

        return $enrollee;
    }

    static function updateQueue(){

    }
}