<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CallView extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                         => $this->id,
            'type'                       => $this->type,
            'is_manual'                  => $this->is_manual,
            'nurse_id'                   => $this->nurse_id,
            'nurse'                      => $this->nurse,
            'patient_id'                 => $this->patient_id,
            'patient'                    => $this->patient,
            'preferred_contact_language' => $this->preferred_contact_language,
            'scheduled_date'             => presentDate($this->scheduled_date, false),
            'last_call'                  => presentDate($this->last_call),
            'practice_id'                => $this->practice_id,
            'practice'                   => $this->practice,
            'state'                      => $this->state,
            'call_time_start'            => $this->call_time_start,
            'call_time_end'              => $this->call_time_end,
            'preferred_call_days'        => $this->preferredCallDaysToString(),
            'scheduler'                  => $this->scheduler,
            'is_ccm'                     => $this->is_ccm,
            'is_bhi'                     => $this->is_bhi,
            'asap'                       => $this->asap,
            'billing_provider'           => $this->billing_provider,
            'ccm_status'                 => $this->ccm_status,
            'patient_nurse_id'           => $this->patient_nurse_id,
            'patient_nurse'              => $this->patient_nurse,
        ];
    }
}