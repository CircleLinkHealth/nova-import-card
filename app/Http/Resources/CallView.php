<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;

class CallView extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                     => $this->id,
            'is_manual'              => $this->is_manual,
            'note_id'                => $this->note_id,
            'nurse_id'               => $this->nurse_id,
            'nurse'                  => $this->nurse,
            'patient_id'             => $this->patient_id,
            'patient'                => $this->patient,
            'scheduled_date'         => $this->scheduled_date,
            'last_call_status'       => $this->lastCallStatus(),
            'last_call'              => $this->last_call,
            'ccm_time'               => $this->ccm_time,
            'bhi_time'               => $this->bhi_time,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'practice_id'            => $this->practice_id,
            'practice'               => $this->practice,
            'call_time_start'        => $this->call_time_start,
            'call_time_end'          => $this->call_time_end,
            'timezone'               => $this->patient_created_at
                ? Carbon::parse($this->patient_created_at)->format('T')
                : null,
            'preferred_call_days'    => $this->preferredCallDaysToString(),
            'patient_status'         => $this->patient_status,
            'provider'               => $this->provider,
            'scheduler'              => $this->scheduler,
        ];
    }
}
