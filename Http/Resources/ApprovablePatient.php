<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use CircleLinkHealth\CcmBilling\Domain\Patient\AutoPatientAttestation;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\Customer\Entities\User;
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

        $ccmTime         = $this->getAllTimeExceptBhi();
        $bhiTime         = $this->getBhiTime();
        $totalTime       = $ccmTime + $bhiTime;
        $autoAttestation = AutoPatientAttestation::fromUser($user)->setMonth($billingStatus->chargeable_month);

        return [
            'id'                     => $user->id,
            'mrn'                    => $user->getMRN(),
            'name'                   => $user->getFullName(),
            'url'                    => route('patient.note.index', ['patientId' => $user->id]),
            'provider'               => $this->getProviderName(),
            'practice'               => $user->primaryPractice->display_name,
            'practice_id'            => $user->primaryPractice->id,
            'dob'                    => $user->getBirthDate(),
            'ccm'                    => round($ccmTime / 60, 2),
            'total_time'             => $totalTime,
            'bhi_time'               => $bhiTime,
            'ccm_time'               => $ccmTime,
            'problems'               => $this->getProblems()->toArray(),
            'no_of_successful_calls' => 0,
            'status'                 => $user->getCcmStatusForMonth($billingStatus->chargeable_month),
            'approve'                => 'approved' === $billingStatus->status,
            'reject'                 => 'rejected' === $billingStatus->status,
            'report_id'              => $billingStatus->id,
            'actor_id'               => $billingStatus->actor_id,
            'qa'                     => 'needs_qa' === $billingStatus->status,
            'chargeable_services'    => $this->getChargeableServices()->toArray(),
            'attested_ccm_problems'  => $autoAttestation->getCcmAttestedProblems()->toArray(),
            'attested_bhi_problems'  => $autoAttestation->getBhiAttestedProblems()->toArray(),
        ];
    }

    private function getAllTimeExceptBhi(): int
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = $this->resource;

        $bhiId = \CircleLinkHealth\Customer\Entities\ChargeableService::cached()
            ->firstWhere('code', \CircleLinkHealth\Customer\Entities\ChargeableService::BHI)
            ->id;

        return $billingStatus->chargeableMonthlyTime
            ->where('chargeable_service_id', '!=', $bhiId)
            ->sum('total_time');
    }

    private function getBhiTime(): int
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = $this->resource;

        $bhiId = \CircleLinkHealth\Customer\Entities\ChargeableService::cached()
            ->firstWhere('code', \CircleLinkHealth\Customer\Entities\ChargeableService::BHI)
            ->id;

        return $billingStatus->chargeableMonthlyTime
            ->where('chargeable_service_id', '=', $bhiId)
            ->sum('total_time');
    }

    private function getChargeableServices(): Collection
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        $services = $user->chargeableMonthlySummaries
            ->map(function (ChargeablePatientMonthlySummary $item) use ($user) {
                /** @var ChargeablePatientMonthlyTime $time */
                $time = $user->chargeableMonthlyTime->firstWhere('chargeable_service_id', $item->chargeable_service_id);

                return [
                    'id'           => $item->chargeable_service_id,
                    'is_fulfilled' => $item->is_fulfilled,
                    'total_time'   => optional($time)->total_time ?? 0,
                ];
            })
            ->values();

        $user->chargeableMonthlyTime->each(function (ChargeablePatientMonthlyTime $time) use ($services) {
            $entry = $services->firstWhere('chargeable_service_id', $time->chargeable_service_id);
            if ( ! $entry) {
                $services->push([
                    'id'           => $time->chargeable_service_id,
                    'is_fulfilled' => false,
                    'total_time'   => $time->total_time,
                ]);
            }
        });

        return $services;
    }

    private function getProblems(): Collection
    {
        /** @var User $user */
        $user = $this->resource->patientUser;

        return $user->ccdProblems->map(function ($prob) {
            return [
                'id'            => $prob->id,
                'name'          => $prob->name,
                'code'          => $prob->icd10Code(),
                'is_behavioral' => $prob->isBehavioral(),
            ];
        })->unique('code')->filter()->values();
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
