<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientMonthlySummary extends JsonResource
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
            'id'                     => $this->id,
            'month_year'             => $this->month_year,
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->ccm_time,
            'no_of_calls'            => $this->no_of_calls,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'approved'               => $this->approved,
            'rejected'               => $this->rejected,
            'actor_id'               => $this->actor_id,
            'billable_problem1'      => $this->billable_problem1,
            'billable_problem2'      => $this->billable_problem2,
            'billable_problem1_code' => $this->billable_problem1_code,
            'billable_problem2_code' => $this->billable_problem2_code,
        ];
    }
}
