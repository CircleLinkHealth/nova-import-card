<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientSuccessfulCallsCountForMonth;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice as PracticeProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\SharedModels\Entities\Call;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApproveBillablePatientsServiceV3
{
    private PracticeProcessor $practiceProcessor;

    public function __construct(PracticeProcessor $practiceProcessor)
    {
        $this->practiceProcessor = $practiceProcessor;
    }

    public function closeMonth(int $actorId, int $practiceId, Carbon $month): bool
    {
        $this->practiceProcessor->closeMonth([$practiceId], $month, $actorId);

        return true;
    }

    public function counts(int $practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        return $this->practiceProcessor->counts([$practiceId], $month);
    }

    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        $pagination     = AppConfig::pull('abp-pagination-size', 20);
        $date           = $date->copy()->startOfMonth();
        $jsonCollection = $this->practiceProcessor->fetchApprovablePatients([$practiceId], $date, $pagination);
        $isClosed       = (bool) collect($jsonCollection->items())->every(fn ($summary) => (bool) $summary['actor_id']);

        return new BillablePatientsForMonthDTO($jsonCollection, $isClosed);
    }

    public function openMonth(int $practiceId, Carbon $month): bool
    {
        $this->practiceProcessor->openMonth([$practiceId], $month);

        return true;
    }

    public function setPatientBillingStatus(int $reportId, string $newStatus)
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = PatientMonthlyBillingStatus::with([
            'patientUser' => fn ($q) => $q->select(['id', 'program_id']),
        ])->find($reportId);
        if ( ! $billingStatus) {
            return null;
        }

        $billingStatus->status   = $newStatus;
        $billingStatus->actor_id = auth()->id();
        $billingStatus->save();

        $counts = $this->counts($billingStatus->patientUser->primaryProgramId(), $billingStatus->chargeable_month)->toArray();

        return [
            'report_id' => $billingStatus->id,
            'counts'    => $counts,
            'status'    => [
                'approved' => 'approved' === $billingStatus->status,
                'rejected' => 'rejected' === $billingStatus->status,
            ],
            'actor_id' => $billingStatus->actor_id,
        ];
    }

    public function setPatientChargeableServices(int $reportId, array $services): bool
    {
        /** @var PatientMonthlyBillingStatus $billingStatus */
        $billingStatus = PatientMonthlyBillingStatus::with([
            'patientUser' => fn ($p) => $p->with('patientInfo')->select(['id', 'program_id']),
        ])->find($reportId);
        if ( ! $billingStatus || ! is_null($billingStatus->actor_id)) {
            return false;
        }

        $locationId = $billingStatus->patientUser->getPreferredContactLocation();
        if (empty($locationId) || ChargeableLocationMonthlySummary::where('location_id', '=', $locationId)
            ->where('chargeable_month', '=', $billingStatus->chargeable_month)
            ->where('is_locked', '=', true)
            ->exists()) {
            return false;
        }

        foreach ($services as $service) {
            $input = (new ForceAttachInputDTO())
                ->setReason('abp')
                ->setPatientUserId($billingStatus->patient_user_id)
                ->setMonth($billingStatus->chargeable_month)
                ->setChargeableServiceId($service['id'])
                ->setActionType($service['action_type']);

            ForcePatientChargeableService::execute($input);
        }

        return true;
    }

    public function successfulCallsCount(array $patientIds, Carbon $month): ResourceCollection
    {
        $arr = Call::whereIn('inbound_cpm_id', $patientIds)
            ->whereBetween('called_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->where('status', '=', Call::REACHED)
            ->groupBy('inbound_cpm_id')
            ->selectRaw('inbound_cpm_id as id, count(id) as count')
            ->get()
            ->map(function (Call $call) {
                $call->setAppends([]);

                return $call->toArray();
            })
            ->toArray();

        return PatientSuccessfulCallsCountForMonth::collection($arr);
    }
}
