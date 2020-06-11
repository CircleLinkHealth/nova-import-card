<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CallView extends Resource
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
            'id'                     => $this->id,
            'type'                   => $this->type,
            'is_manual'              => $this->is_manual,
            'nurse_id'               => $this->nurse_id,
            'nurse'                  => $this->nurse,
            'patient_id'             => $this->patient_id,
            'patient'                => $this->patient,
            'scheduled_date'         => presentDate($this->scheduled_date, false),
            'last_call'              => presentDate($this->last_call),
            'ccm_time'               => $this->ccm_time,
            'bhi_time'               => $this->bhi_time,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'practice_id'            => $this->practice_id,
            'practice'               => $this->practice,
            'state'                  => $this->state,
            'call_time_start'        => $this->call_time_start,
            'call_time_end'          => $this->call_time_end,
            'preferred_call_days'    => $this->preferredCallDaysToString(),
            'scheduler'              => $this->scheduler,
            'is_ccm'                 => $this->is_ccm,
            'is_bhi'                 => $this->is_bhi,
            'asap'                   => $this->asap,
            'billing_provider'       => $this->billing_provider,
            'ccm_status'             => $this->ccm_status,
            'patient_nurse_id'       => $this->patient_nurse_id,
            'patient_nurse'          => $this->patient_nurse,
        ];
    }
}
