<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\Resource;

class ApprovableBillablePatient extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $bP = $this->patient
            ->careTeamMembers
            ->where('type', '=', 'billing_provider')
            ->first();

        $name = $this->patient->fullName;
        $url  = route('patient.note.index', [
            'patient' => $this->patient->id,
        ]);

        return [
            'id'                     => $this->patient->id,
            'mrn'                    => $this->patient->patientInfo->mrn_number,
            'name'                   => $name,
            'url'                    => $url,
            'provider'               => $bP
                ? optional($bP->user)->fullName
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'dob'                    => $this->patient->patientInfo->birth_date,
            'ccm'                    => round($this->ccm_time / 60, 2),
            'problem1'               => $this->billable_problem1,
            'problem1_code'          => $this->billable_problem1_code,
            'problem2'               => $this->billable_problem2,
            'problem2_code'          => $this->billable_problem2_code,
            'problems'               => $this->allCcdProblems($this->patient),
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $this->patient->patientInfo->ccm_status,
            'approve'                => $this->approved,
            'reject'                 => $this->rejected,
            'report_id'              => $this->id,
            'qa'                     => $this->needs_qa,

            'chargeable_services' => ChargeableService::collection($this->whenLoaded('chargeableServices')),

        ];
    }

    public function allCcdProblems(User $patient)
    {
        return $patient->ccdProblems->map(function ($prob) {
            return [
                'id'   => $prob->id,
                'name' => $prob->name,
                'code' => $prob->icd10Code(),
            ];
        });
    }
}
