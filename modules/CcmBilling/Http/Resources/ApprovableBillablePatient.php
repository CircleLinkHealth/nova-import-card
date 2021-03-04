<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService as ChargeableServiceModel;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

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
            'ccm'                    => round($this->getBillableCcmCs() / 60, 2),
            'total_time'             => $this->total_time,
            'bhi_time'               => $this->bhi_time,
            'ccm_time'               => $this->getBillableCcmCs(),
            'problems'               => $problems,
            'no_of_successful_calls' => $this->no_of_successful_calls,
            'status'                 => $status,
            'approve'                => (bool) $this->approved,
            'reject'                 => (bool) $this->rejected,
            'report_id'              => $this->id,
            'actor_id'               => $this->actor_id,
            'qa'                     => $this->needs_qa && ! $this->approved && ! $this->rejected,
            'attested_ccm_problems'  => $this->getCcmAttestedProblems($problems),
            'chargeable_services'    => $this->getChargeableServices()->toArray($request),
            'attested_bhi_problems'  => $this->getBhiAttestedProblems(),
        ];
    }

    private function getBhiAttestedProblems()
    {
        return $this->bhiAttestedProblems()->unique()->pluck('id')->toArray();
    }

    private function getCcmAttestedProblems(Collection $allProblems)
    {
        if ($this->hasServiceCode(ChargeableServiceModel::RPM)) {
            return $allProblems->pluck('id')->toArray();
        }

        return $this->ccmAttestedProblems()->unique()->pluck('id')->toArray();
    }

    private function getChargeableServices(): AnonymousResourceCollection
    {
        return ChargeableServiceForAbp::collectionFromPms($this->resource);
    }
}
