<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Domain\Patient\AutoPatientAttestation;
use CircleLinkHealth\CcmBilling\Domain\Patient\ClashingChargeableServices;
use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ApprovablePatient extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function toArray($request)
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = $this->resource;
        $user          = $billingStatus->patientUser;

        $ccmTime         = $this->getBillableCcmCs();
        $bhiTime         = $this->getBhiTime();
        $totalTime       = $this->getAllTimeExceptBhi() + $bhiTime;
        $autoAttestation = AutoPatientAttestation::fromUser($user)->setMonth($billingStatus->chargeable_month)->setBillingRevamp(true);

        return [
            'id'          => $user->id,
            'mrn'         => $user->getMRN(),
            'name'        => $user->getFullName(),
            'url'         => route('patient.note.index', ['patientId' => $user->id]),
            'provider'    => $this->getProviderName(),
            'practice'    => $user->primaryPractice->display_name,
            'practice_id' => $user->primaryPractice->id,
            'dob'         => $user->getBirthDate(),

            // only for backwards compatibility
            'ccm'        => round($ccmTime / 60, 2),
            'total_time' => $totalTime,
            'bhi_time'   => $bhiTime,
            'ccm_time'   => $ccmTime,

            'problems'               => $this->getProblems($autoAttestation)->values()->toArray(),
            'no_of_successful_calls' => 0,
            'status'                 => $user->getCcmStatusForMonth($billingStatus->chargeable_month),
            'approve'                => PatientMonthlyBillingStatus::APPROVED === $billingStatus->status,
            'reject'                 => PatientMonthlyBillingStatus::REJECTED === $billingStatus->status,
            'report_id'              => $billingStatus->id,
            'actor_id'               => $billingStatus->actor_id,
            'qa'                     => PatientMonthlyBillingStatus::NEEDS_QA === $billingStatus->status,
            'chargeable_services'    => $this->getChargeableServices()->values()->toArray($request),
            'attested_ccm_problems'  => $autoAttestation->getCcmAttestedProblems()->values()->toArray(),
            'attested_bhi_problems'  => $autoAttestation->getBhiAttestedProblems()->values()->toArray(),
        ];
    }

    private function getAllTimeExceptBhi()
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        $bhiId = \CircleLinkHealth\Customer\Entities\ChargeableService::getChargeableServiceIdUsingCode(\CircleLinkHealth\Customer\Entities\ChargeableService::BHI);

        return $user->chargeableMonthlyTime
            ->where('chargeable_service_id', '!=', $bhiId)
            ->sum('total_time');
    }

    private function getBhiTime(): int
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        $bhiId = \CircleLinkHealth\Customer\Entities\ChargeableService::getChargeableServiceIdUsingCode(\CircleLinkHealth\Customer\Entities\ChargeableService::BHI);

        return $user->chargeableMonthlyTime
            ->where('chargeable_service_id', '=', $bhiId)
            ->sum('total_time');
    }

    private function getBillableCcmCs(): int
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = $this->resource;

        return ClashingChargeableServices::getCcmTimeForLegacyReportsInPriority($billingStatus->patientUser);
    }

    private function getChargeableServices(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        return ChargeableServiceForAbp::collectionFromChargeableMonthlySummaries($user);
    }

    /**
     * @param AutoPatientAttestation $attestation
     *
     * Todo: cleanup in the future.
     * We've had issues where a patient has a duplicate problem i.e. 3 Cholesterol problems. One of those was attested for.
     * But during the unique('code') check (which is done for the attestation modal), it gets filtered out.
     * So we put the attested problems first in the collection so we make sure they're there for ABP and they appear attested.
     *
     *
     * @return Collection
     * @throws \Exception
     */
    private function getProblems(AutoPatientAttestation $attestation): Collection
    {
        /** @var User $user */
        $user = $this->resource->patientUser;
        $problems = $user->ccdProblems->sortByDesc('is_monitored');

        $ccmAttestedProblemIds = $attestation->getCcmAttestedProblems();
        $bhiAttestedProblemIds = $attestation->getBhiAttestedProblems();
        $ccmAttestedProblems = $problems->whereIn('id', $ccmAttestedProblemIds);
        $bhiAttestedProblems = $problems->whereIn('id', $bhiAttestedProblemIds);
        $problems = $ccmAttestedProblems->merge($bhiAttestedProblems)->merge($problems)->unique('code')->filter()->values();

        return $problems->map(function ($prob) {
            return [
                'id'            => $prob->id,
                'name'          => $prob->name,
                'code'          => $prob->icd10Code(),
                'is_behavioral' => $prob->isBehavioral(),
            ];
        })->unique('code')->filter();
    }

    private function getProviderName(): ?string
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        $bP = $user
            ->billingProviderUser();

        return $bP
            ? optional($bP)->getFullName()
            : '';
    }
}
