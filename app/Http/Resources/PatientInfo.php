<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientInfo extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                                  => $this->id,
            'birth_date'                          => $this->birth_date,
            'ccm_status'                          => $this->ccm_status,
            'gender'                              => $this->gender,
            'general_comment'                     => $this->general_comment,
            'last_call_status'                    => $this->last_call_status,
            'last_contact_time'                   => $this->last_contact_time,
            'last_successful_contact_time'        => $this->last_successful_contact_time,
            'no_call_attempts_since_last_success' => $this->no_call_attempts_since_last_success,
            'contact_windows'                     => PatientContactWindows::collection($this->whenLoaded('contactWindows')),
        ];
    }
}
