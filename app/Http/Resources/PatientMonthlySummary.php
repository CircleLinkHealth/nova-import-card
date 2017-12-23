<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class PatientMonthlySummary extends Resource
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
            'month_year'             => $this->month_year,
            'ccm_time'               => $this->ccm_time,
            'no_of_calls'            => $this->no_of_calls,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'patient_info_id'        => $this->patient_info_id,
            'is_ccm_complex'         => $this->is_ccm_complex,
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
