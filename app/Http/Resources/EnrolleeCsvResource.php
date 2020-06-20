<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EnrolleeCsvResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return string
     */
    public function toArray($request)
    {
        return '"'.$this->id.'",'.
            '"'.$this->user_id.'",'.
            '"'.$this->mrn.'",'.
            '"'.$this->first_name.'",'.
            '"'.$this->last_name.'",'.
            '"'.$this->care_ambassador_name.'",'.
            '"'.$this->status.'",'.
            '"'.$this->source.'",'.
            '"'.$this->enrollment_non_responsive.'",'.
            '"'.$this->auto_enrollment_triggered.'",'.
            '"'.$this->practice_name.'",'.
            '"'.$this->provider_name.'",'.
            '"'.$this->lang.'",'.
            '"'.$this->requested_callback.'",'.
            '"'.secondsToMMSS($this->total_time_spent).'",'.
            '"'.$this->attempt_count.'",'.
            '"'.$this->last_attempt_at.'",'.
            '"'.$this->last_call_outcome.'",'.
            '"'.$this->last_call_outcome_reason.'",'.
            '"'.$this->address.'",'.
            '"'.$this->address_2.'",'.
            '"'.$this->city.'",'.
            '"'.$this->state.'",'.
            '"'.$this->zip.'",'.
            '"'.$this->primary_phone.'",'.
            '"'.$this->home_phone.'",'.
            '"'.$this->cell_phone.'",'.
            '"'.$this->other_phone.'",'.
            '"'.$this->dob.'",'.
            '"'.$this->preferred_days.'",'.
            '"'.$this->preferred_window.'",'.
            '"'.$this->primary_insurance.'",'.
            '"'.$this->secondary_insurance.'",'.
            '"'.$this->tertiary_insurance.'",'.
            '"'.$this->has_copay.'",'.
            '"'.$this->email.'",'.
            '"'.$this->last_encounter.'",'.
            '"'.$this->created_at.'",'.
            '"'.$this->updated_at.'"';
    }
}
