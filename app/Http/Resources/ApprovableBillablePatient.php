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
        $problem1     = isset($this->problem_1) && $this->patient->ccdProblems
            ? $this->patient->ccdProblems->where('id', $this->problem_1)->first()
            : null;
        $problem1Code = isset($problem1)
            ? $problem1->icd10Code()
            : null;
        $problem1Name = $problem1->name ?? null;

        $problem2     = isset($this->problem_2) && $this->patient->ccdProblems
            ? $this->patient->ccdProblems->where('id', $this->problem_2)->first()
            : null;
        $problem2Code = isset($problem2)
            ? $problem2->icd10Code()
            : null;
        $problem2Name = $problem2->name ?? null;

        $lacksProblems = ! $problem1Code || ! $problem2Code || ! $problem1Name || ! $problem2Name;

        $toQA = ( ! $this->approved && ! $this->rejected)
                || $lacksProblems
                || $this->no_of_successful_calls == 0
                || in_array($this->patient->patientInfo->ccm_status, ['withdrawn', 'paused']);

        if (($this->rejected || $this->approved) && $this->actor_id) {
            $toQA = false;
        }

        if ($toQA) {
            $this->approved = $this->rejected = false;
        }

        $bP = $this->patient
            ->careTeamMembers
            ->where('type', '=', 'billing_provider')
            ->first();

        $name = $this->patient->fullName;
        $url = route('patient.careplan.show', [
            'patient' => $this->patient->id,
            'page'    => 1,
        ]);

        return [
            'id'                     => $this->patient->id,
            'mrn'                    => $this->patient->patientInfo->mrn_number,
            'name'                   => $name,
            'url'                   => $url,
            'provider'               => $bP
                ? optional($bP->user)->fullName
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'dob'                    => $this->patient->patientInfo->birth_date,
            'ccm'                    => round($this->ccm_time / 60, 2),
            'problem1'               => $problem1Name,
            'problem1_code'          => $problem1Code,
            'problem2'               => $problem2Name,
            'problem2_code'          => $problem2Code,
            'problems'               => $this->allCcdProblems($this->patient),
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $this->patient->patientInfo->ccm_status,
            'approve'                => $this->approved,
            'reject'                 => $this->rejected,
            'report_id'              => $this->id,
            'qa'                     => $toQA,
            'lacksProblems'          => $lacksProblems,

            'chargeable_services'    => $this->chargeableServices()->get(),

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
