<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Resources\Json\Resource;

class ApprovableBillablePatient extends Resource
{
    public function allCcdProblems(User $patient)
    {
        return $patient->ccdProblems->map(function ($prob) {
            return [
                'id'            => $prob->id,
                'name'          => $prob->name,
                'code'          => $prob->icd10Code(),
                'is_behavioral' => $prob->isBehavioral(),
            ];
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $bP = $this->patient
            ->careTeamMembers
            ->where('type', '=', 'billing_provider')
            ->first();

        $name = $this->patient->getFullName();
        $url  = route('patient.note.index', [
            'patient' => $this->patient->id,
        ]);


        $attestedBhiProblems = [];
        if ($this->hasServiceCode('CPT 99484')) {
            $attestedBhiProblems = $this->attestedProblems->where('cpmProblem.is_behavioral', '=', true)->pluck('id');
        }

        $status = $this->closed_ccm_status;
        if (null == $status) {
            $status = $this->patient->patientInfo->ccm_status;
        }

        if (Carbon::parse($this->month_year)->lt(Carbon::parse(PatientMonthlySummary::DATE_ATTESTED_CONDITIONS_ENABLED)) && $this->attestedProblems()->count() == 0) {
            $attestedProblems = collect([
                optional($this->billableProblem1)->id,
                optional($this->billableProblem2)->id,
            ])->filter()->toArray();
        } else {
            $attestedCcmProblems = $this->attestedProblems->where('cpmProblem.is_behavioral', '=', false)->pluck('id');
        }

        return [
            'id'                     => $this->patient->id,
            'mrn'                    => $this->patient->getMRN(),
            'name'                   => $name,
            'url'                    => $url,
            'provider'               => $bP
                ? optional($bP->user)->getFullName()
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'practice_id'            => $this->patient->primaryPractice->id,
            'dob'                    => $this->patient->getBirthDate(),
            'ccm'                    => round($this->ccm_time / 60, 2),
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->ccm_time,
            'problems'               => $this->allCcdProblems($this->patient),
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $status,
            'approve'                => $this->approved,
            'reject'                 => $this->rejected,
            'report_id'              => $this->id,
            'actor_id'               => $this->actor_id,
            'qa'                     => $this->needs_qa || ( ! $this->approved && ! $this->rejected),
            'attested_ccm_problems'  => $attestedCcmProblems,
            'chargeable_services'    => ChargeableService::collection($this->whenLoaded('chargeableServices')),
            'attested_bhi_problems'  => $attestedBhiProblems,
        ];
    }
}
