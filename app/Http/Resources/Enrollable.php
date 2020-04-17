<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;


use App\CareAmbassadorLog;
use App\TrixField;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Resources\Json\Resource;

class Enrollable extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        //enrollable is enrollee at this point
        $enrollable = $this->resource;

        if (! $enrollable){
            return [];
        }

        $careAmbassador = $this->careAmbassador->careAmbassador;

        $enrollable->load(['practice']);

        return [
            'enrollable_id' => $enrollable->id,
            'enrollable_user_id' => optional($enrollable->user)->id,
            'practice' => $enrollable->practice->toArray(),
            'last_call_outcome' => $enrollable->last_call_outcome ?? '',
            'last_call_outcome_reason' => $enrollable->last_call_outcome_reason ?? '',
            'name' => $enrollable->first_name . ' ' . $enrollable->last_name,
            'lang' => $enrollable->lang,
            'practice_id' => $enrollable->practice->id,
            'practice_name' => $enrollable->practice->display_name,
            'practice_phone' => $enrollable->practice->outgoing_phone_number,
            'other_phone' => $enrollable->other_phone,
            'cell_phone' => $enrollable->cell_phone,
            'home_phone' => $enrollable->home_phone,

            //we need to prefill these per CPM-2256 for confirmed family members
            'utc_reason' => $enrollable->status === Enrollee::UNREACHABLE && $enrollable->last_call_outcome ? $enrollable->last_call_outcome : '',
            'reason' => $enrollable->status !== Enrollee::UNREACHABLE  && $enrollable->last_call_outcome ? $enrollable->last_call_outcome : '',
            'utc_reason_other' => $enrollable->status === Enrollee::UNREACHABLE && $enrollable->last_call_outcome_reason ? $enrollable->last_call_outcome_reason : '',
            'reason_other' => $enrollable->status !== Enrollee::UNREACHABLE && $enrollable->last_call_outcome_reason ? $enrollable->last_call_outcome_reason : '',
            'last_encounter' => $enrollable->last_encounter ?? 'N/A',
            'attempt_count' => $enrollable->attempt_count ?? 0,
            'last_attempt_at' => optional($enrollable->last_attempt_at)->toDateString() ?? 'N/A',
            'address' => $enrollable->address,
            'address_2' => $enrollable->address_2,
            'state' => $enrollable->state,
            'zip' => $enrollable->zip,
            'email' => $enrollable->email,
            'city' => $enrollable->city,
            'dob' => optional($enrollable->dob)->toDateString() ?? 'N/A',



            'report'   => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
            'script'   => TrixField::careAmbassador($this->lang)->first(),

            'provider' => $this->provider->toArray(),
            'provider_phone' => $this->provider->getPhone(),
            'has_tips' => !! $this->practice->enrollmentTips
        ];

    }
}