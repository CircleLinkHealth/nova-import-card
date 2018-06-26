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

        $problems = [
            'bhi_problem'      => 'N/A',
            'bhi_problem_code' => 'N/A',
            'problem1'         => 'N/A',
            'problem1_code'    => 'N/A',
            'problem2'         => 'N/A',
            'problem2_code'    => 'N/A',
        ];

        if ($this->hasServiceCode('CPT 99484')) {
            $bhiProblem = $this->billableBhiProblems()->first();

            if ($bhiProblem) {
                $problems['bhi_problem']      = $bhiProblem->pivot->name ?? null;
                $problems['bhi_problem_code'] = $bhiProblem->pivot->icd_10_code ?? null;
            }
        }

        $problems['problem1']      = $this->billable_problem1;
        $problems['problem1_code'] = $this->billable_problem1_code;
        $problems['problem2']      = $this->billable_problem2;
        $problems['problem2_code'] = $this->billable_problem2_code;

        return array_merge([
            'id'                     => $this->patient->id,
            'mrn'                    => $this->patient->patientInfo->mrn_number,
            'name'                   => $name,
            'url'                    => $url,
            'provider'               => $bP
                ? optional($bP->user)->fullName
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'practice_id'            => $this->patient->primaryPractice->id,
            'dob'                    => $this->patient->patientInfo->birth_date,
            'ccm'                    => round($this->ccm_time / 60, 2),
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->ccm_time,
            'problems'               => $this->allCcdProblems($this->patient),
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $this->patient->patientInfo->ccm_status,
            'approve'                => $this->approved,
            'reject'                 => $this->rejected,
            'report_id'              => $this->id,
            'actor_id'               => $this->actor_id,
            'qa'                     => $this->needs_qa,

            'chargeable_services' => ChargeableService::collection($this->whenLoaded('chargeableServices')),

        ], $problems);
    }

    public function allCcdProblems(User $patient)
    {
        return $patient->ccdProblems->map(function ($prob) {
            return [
                'id'   => $prob->id,
                'name' => $prob->name,
                'code' => $prob->icd10Code(),
                'is_behavioral' => $prob->isBehavioral()
            ];
        });
    }
}
