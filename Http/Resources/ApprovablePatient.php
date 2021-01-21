<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Http\Resources;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\AutoPatientAttestation;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
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
        /** @var User $user */
        $user = $this->resource;

        $monthYear       = $this->getMonthYearFromRelations();
        $ccmTime         = $this->getAllTimeExceptBhi();
        $bhiTime         = $this->getBhiTime();
        $totalTime       = $ccmTime + $bhiTime;
        $billingStatus   = optional($this->getBillingStatus());
        $autoAttestation = AutoPatientAttestation::fromUser($user)->setMonth($monthYear);

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
            'no_of_successful_calls' => $this->getNumberOfSuccessfulCalls(),
            'status'                 => $user->getCcmStatusForMonth($monthYear),
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
        /** @var User $user */
        $user = $this->resource;

        return $user->chargeableMonthlySummariesView
            ->where('chargeable_service_code', '!=', \CircleLinkHealth\Customer\Entities\ChargeableService::BHI)
            ->sum('total_time');
    }

    private function getBhiTime(): int
    {
        /** @var User $user */
        $user = $this->resource;

        return $user->chargeableMonthlySummariesView
            ->where('chargeable_service_code', '=', \CircleLinkHealth\Customer\Entities\ChargeableService::BHI)
            ->sum('total_time');
    }

    private function getBillingStatus(): ?PatientMonthlyBillingStatus
    {
        /** @var User $user */
        $user = $this->resource;
        if ( ! $user->monthlyBillingStatus || $user->monthlyBillingStatus->isEmpty()) {
            return null;
        }

        return $user->monthlyBillingStatus->first();
    }

    private function getChargeableServices(): Collection
    {
        /** @var User $user */
        $user = $this->resource;

        return $user->chargeableMonthlySummariesView
            ->filter(fn (ChargeablePatientMonthlySummaryView $item) => $item->is_fulfilled)
            ->map(function (ChargeablePatientMonthlySummaryView $view) {
                return [
                    'id'   => $view->chargeable_service_id,
                    'code' => $view->chargeable_service_code,
                ];
            });
    }

    private function getMonthYearFromRelations(): Carbon
    {
        /** @var User $user */
        $user = $this->resource;

        if ($user->relationLoaded('monthlyBillingStatus') && $user->monthlyBillingStatus->isNotEmpty()) {
            /** @var PatientMonthlyBillingStatus $ccmStatus */
            $billingStatus = $user->monthlyBillingStatus->first();

            return $billingStatus->chargeable_month;
        }

        if ($user->relationLoaded('endOfMonthCcmStatusLogs') && $user->endOfMonthCcmStatusLogs->isNotEmpty()) {
            /** @var EndOfMonthCcmStatusLog $ccmStatus */
            $ccmStatus = $user->endOfMonthCcmStatusLogs->first();

            return $ccmStatus->chargeable_month;
        }

        if ($user->relationLoaded('chargeableMonthlySummaries') && $user->chargeableMonthlySummaries->isNotEmpty()) {
            /** @var ChargeablePatientMonthlySummary $cms */
            $cms = $user->chargeableMonthlySummaries->first();

            return $cms->chargeable_month;
        }

        return now()->startOfMonth();
    }

    private function getNumberOfSuccessfulCalls()
    {
        /** @var User $user */
        $user = $this->resource;

        return optional($user->chargeableMonthlySummariesView
            ->first())->no_of_successful_calls;
    }

    private function getProblems(): Collection
    {
        /** @var User $user */
        $user = $this->resource;

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
        $user = $this->resource;

        $bP = $user
            ->careTeamMembers
            ->where('type', '=', 'billing_provider')
            ->first();

        return $bP
            ? optional($bP->user)->getFullName()
            : '';
    }
}
