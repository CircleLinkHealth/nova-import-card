<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientSuccessfulCallsCountForMonth;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice as PracticeProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\Call;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApproveBillablePatientsServiceV3
{
    private PracticeProcessor $practiceProcessor;

    public function __construct(PracticeProcessor $practiceProcessor)
    {
        $this->practiceProcessor = $practiceProcessor;
    }

    public function closeMonth(int $actorId, int $practiceId, Carbon $month)
    {
        return $this->practiceProcessor->closeMonth($actorId, $practiceId, $month);
    }

    public function counts(int $practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        return $this->practiceProcessor->counts($practiceId, $month);
    }

    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        $pagination = AppConfig::pull('abp-pagination-size', 20);
        $date       = $date->copy()->startOfMonth();
        //todo: change this to call FetchApprovablePatients action class (which should use the practiceProcessor internally)
        $jsonCollection = $this->practiceProcessor->fetchApprovablePatients($practiceId, $date, $pagination);
        $isClosed       = (bool) $jsonCollection->collection->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );

        return new BillablePatientsForMonthDTO($jsonCollection->resource, $isClosed);
    }

    public function openMonth(int $practiceId, Carbon $month)
    {
        return $this->practiceProcessor->openMonth($practiceId, $month);
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

    public function setPatientChargeableServices(int $reportId, array $csIds, Carbon $month = null): bool
    {
        //todo: Modify Patient Activity
        $billingStatus = PatientMonthlyBillingStatus::find($reportId);
        if ( ! $billingStatus) {
            return false;
        }

        $patientId     = $billingStatus->patient_user_id;
        $coll          = collect($csIds);
        $bhiId         = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::BHI)->id;
        $indefiniteIds = $coll->reject(fn ($id) => $id == $bhiId);
        $indefiniteIds->each(fn ($id) => ForcePatientChargeableService::force($patientId, $id));
        if ($coll->has($bhiId)) {
            ForcePatientChargeableService::force(
                $patientId,
                $bhiId,
                $month ?? now()->subMonth()->startOfMonth()
            );
        }

        return true;
    }

    public function successfulCallsCount(array $patientIds, Carbon $month): ResourceCollection
    {
        $arr = Call::whereIn('inbound_cpm_id', $patientIds)
            ->whereBetween('called_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->where('status', '=', 'reached')
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
