<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApprovablePatient extends JsonResource
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
        return [
            'id'       => $this->patient->id,
            'mrn'      => $this->patient->getMRN(),
            'name'     => '$name',
            'url'      => '$url',
            'provider' => '$bP'
                ? 'optional($bP->user)->getFullName()'
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'practice_id'            => $this->patient->primaryPractice->id,
            'dob'                    => $this->patient->getBirthDate(),
            'ccm'                    => round($this->ccm_time / 60, 2),
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->ccm_time,
            'problems'               => '$problems',
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => '$status',
            'approve'                => (bool) $this->approved,
            'reject'                 => (bool) $this->rejected,
            'report_id'              => $this->id,
            'actor_id'               => $this->actor_id,
            'qa'                     => $this->needs_qa && ! $this->approved && ! $this->rejected,
            'attested_ccm_problems'  => '',
            'chargeable_services'    => ChargeableService::collection($this->whenLoaded('chargeableServices')),
            'attested_bhi_problems'  => '',
        ];
    }
}
