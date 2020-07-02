<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovableBillablePatient extends JsonResource
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
            $this->patient->id,
        ]);

        $status = $this->closed_ccm_status;
        if (null == $status) {
            $status = $this->patient->patientInfo->getCcmStatusForMonth(Carbon::parse($this->month_year));
        }
        $problems = $this->allCcdProblems($this->patient)->unique('code')->filter()->values();

        return [
            'id'       => $this->patient->id,
            'mrn'      => $this->patient->getMRN(),
            'name'     => $name,
            'url'      => $url,
            'provider' => $bP
                ? optional($bP->user)->getFullName()
                : '',
            'practice'               => $this->patient->primaryPractice->display_name,
            'practice_id'            => $this->patient->primaryPractice->id,
            'dob'                    => $this->patient->getBirthDate(),
            'ccm'                    => round($this->ccm_time / 60, 2),
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->ccm_time,
            'problems'               => $problems,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $status,
            'approve'                => (bool) $this->approved,
            'reject'                 => (bool) $this->rejected,
            'report_id'              => $this->id,
            'actor_id'               => $this->actor_id,
            'qa'                     => $this->needs_qa && ! $this->approved && ! $this->rejected,
            'attested_ccm_problems'  => $this->ccmAttestedProblems()->unique()->pluck('id'),
            'chargeable_services'    => ChargeableService::collection($this->whenLoaded('chargeableServices')),
            'attested_bhi_problems'  => $this->bhiAttestedProblems()->unique()->pluck('id'),
        ];
    }
}
