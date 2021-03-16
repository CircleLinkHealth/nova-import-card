<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovableBillablePatient;
use CircleLinkHealth\CcmBilling\Http\Resources\ChargeableServiceForAbp;
use CircleLinkHealth\CcmBilling\Http\Resources\SetPatientChargeableServicesResponse;
use CircleLinkHealth\CcmBilling\Jobs\SetLegacyPmsClosedMonthStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\SharedModels\Repositories\BillablePatientsEloquentRepository;
use CircleLinkHealth\SharedModels\Repositories\PatientSummaryEloquentRepository;

/**
 * @deprecated Replaced with {@link ApproveBillablePatientsServiceV3}
 *
 * Class ApproveBillablePatientsService
 */
class ApproveBillablePatientsService
{
    public $approvePatientsRepo;
    public $patientSummaryRepo;

    public function __construct(
        BillablePatientsEloquentRepository $approvePatientsRepo,
        PatientSummaryEloquentRepository $patientSummaryRepo
    ) {
        $this->approvePatientsRepo = $approvePatientsRepo;
        $this->patientSummaryRepo  = $patientSummaryRepo;
    }

    public function attachDefaultChargeableService($summary, $defaultCodeId = null, $detach = false)
    {
        return $this->patientSummaryRepo->attachChargeableService($summary, $defaultCodeId, $detach);
    }

    public function billablePatientSummaries($practiceId, Carbon $month)
    {
        return $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month);
    }

    public function closeMonth(int $actorId, $practiceId, Carbon $month)
    {
        $updated = PatientMonthlySummary::whereHas('patient', function ($q) use ($practiceId) {
            $q->ofPractice($practiceId);
        })
            ->where('month_year', $month)
            ->update([
                'actor_id' => $actorId,
                'needs_qa' => false,
            ]);

        SetLegacyPmsClosedMonthStatus::dispatch($practiceId, $month)
            ->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE));

        return $updated;
    }

    public function counts($practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        // the counts might be inaccurate here because the records might
        // not be processed yet. see command ProcessApprovableBillablePatientSummary

        $approved = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', true)
            ->where(function ($sq) {
                $sq->where('rejected', '=', false)
                    ->orWhereNull('rejected');
            })
            ->count();

        $toQA = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', false)
            ->where(function ($sq) {
                $sq->where('rejected', '=', false)
                    ->orWhereNull('rejected');
            })
            ->where('needs_qa', '=', true)
            ->count();

        $rejected = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('rejected', '=', true)
            ->where('approved', '=', false)
            ->count();

        // 1. not all fields might have been set, because they might not have been processed yet
        // 2. or we have an actor_id but none of these is true
        $other = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where(function ($sq) {
                $sq->where('rejected', '=', false)
                    ->orWhereNull('rejected');
            })
            ->where('approved', '=', false)
            ->where(function ($sq) {
                $sq->where('needs_qa', '=', false)
                    ->orWhereNull('needs_qa');
            })
            ->count();

        return new BillablePatientsCountForMonthDTO($approved, $toQA, $rejected, $other);
    }

    public function detachDefaultChargeableService($summary, $defaultCodeId)
    {
        return $this->patientSummaryRepo->detachChargeableService($summary, $defaultCodeId);
    }

    /**
     * Returns a collection containing information about billable patients for a practice for a month.
     *
     * The elements of the collection are:
     *  summaries LengthAwarePaginator
     *  is_closed Boolean
     *
     * @param $practiceId
     */
    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        // 1. this will fetch billable patients that have
        //    ccm > 1200 and/or bhi > 1200
        $summaries = $this->billablePatientSummaries(
            $practiceId,
            $date
        )->paginate(AppConfig::pull('abp-pagination-size', 20));

        //note: this only applies to the paginated results, not the whole collection. not sure if intended
        $summaries->getCollection()->transform(
            function ($summary) {
                if ( ! $summary->actor_id) {
                    $aSummary = $this->patientSummaryRepo->attachChargeableServices($summary);
                    $summary = $this->patientSummaryRepo->setApprovalStatusAndNeedsQA($aSummary);
                }

                return ApprovableBillablePatient::make($summary);
            }
        );

        $isClosed = (bool) $summaries->getCollection()->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );

        return new BillablePatientsForMonthDTO($summaries, $isClosed);
    }

    public function openMonth($practiceId, Carbon $month)
    {
        return PatientMonthlySummary::whereHas('patient', function ($q) use ($practiceId) {
            $q->ofPractice($practiceId);
        })
            ->where('month_year', $month)
            ->update([
                'actor_id'          => null,
                'closed_ccm_status' => null,
            ]);
    }

    public function patientChargeableServicesInputContainsClashes(array $input): bool
    {
        return false;
    }

    public function setPatientBillingStatus(int $reportId, string $newStatus): ?array
    {
        /** @var PatientMonthlySummary $summary */
        $summary = PatientMonthlySummary::with([
            'patient' => fn ($q) => $q->select(['id', 'program_id']),
        ])->find($reportId);
        if ( ! $summary) {
            return null;
        }

        $summary->approved = 'approved' === $newStatus;
        $summary->rejected = 'rejected' === $newStatus;

        if ( ! $summary->approved && ! $summary->rejected) {
            $summary->needs_qa = true;
        }

        //if approved was unchecked, rejected stays as is. If it was approved, rejected becomes 0
        $summary->actor_id = auth()->id();
        $summary->save();

        $counts = $this->counts(intval($summary->patient->primaryProgramId()), $summary->month_year)->toArray();

        return [
            'report_id' => $summary->id,
            'counts'    => $counts,
            'status'    => [
                'approved' => $summary->approved,
                'rejected' => $summary->rejected,
            ],
            'actor_id' => $summary->actor_id,
        ];
    }

    public function setPatientChargeableServices(int $reportId, array $services): SetPatientChargeableServicesResponse
    {
        $summary = PatientMonthlySummary::find($reportId);
        if ( ! $summary) {
            return SetPatientChargeableServicesResponse::make([]);
        }

        $summary->actor_id = auth()->id();
        $summary->save();

        $toSync = [];

        collect($services)
            ->filter(fn ($service) => ($service['selected'] ?? false) === true)
            ->each(function ($service) use (&$toSync) {
                $toSync[$service['id']] = ['is_fulfilled' => true];
            });

        $summary->chargeableServices()->sync($toSync);
        $summary->load('chargeableServices');

        $result = [
            'approved'            => (bool) $summary->approved,
            'rejected'            => (bool) $summary->rejected,
            'qa'                  => $summary->needs_qa && ! $summary->approved && ! $summary->rejected,
            'chargeable_services' => ChargeableServiceForAbp::collectionFromPms($summary),
        ];

        return SetPatientChargeableServicesResponse::make($result);
    }

    public function setPracticeChargeableServices(int $practiceId, Carbon $month, int $defaultCodeId, bool $isDetach)
    {
        return $this
            ->billablePatientSummaries($practiceId, $month)
            ->get()
            ->map(function ($summary) use ($defaultCodeId, $isDetach) {
                if ( ! $isDetach) {
                    $summary = $this
                        ->attachDefaultChargeableService($summary, $defaultCodeId, false);
                } else {
                    $summary = $this
                        ->detachDefaultChargeableService($summary, $defaultCodeId);
                }

                return ApprovableBillablePatient::make($summary);
            });
    }
}
