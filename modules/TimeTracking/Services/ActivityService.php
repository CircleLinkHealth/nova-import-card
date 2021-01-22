<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TimeTracking\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Customer\LocationServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\ChargeableServiceDuration;
use CircleLinkHealth\SharedModels\Repositories\ActivityRepository;
use CircleLinkHealth\SharedModels\Repositories\CallRepository;
use CircleLinkHealth\SharedModels\Repositories\PatientSummaryEloquentRepository;
use Illuminate\Support\Facades\Log;

class ActivityService
{
    protected $callRepo;
    protected $repo;
    /**
     * @var PatientSummaryEloquentRepository
     */
    private $patientSummaryEloquentRepository;

    public function __construct(
        ActivityRepository $repo,
        CallRepository $callRepo,
        PatientSummaryEloquentRepository $patientSummaryEloquentRepository
    ) {
        $this->repo                             = $repo;
        $this->callRepo                         = $callRepo;
        $this->patientSummaryEloquentRepository = $patientSummaryEloquentRepository;
    }

    /**
     * Get the CCM Time provided by a specific provider to a specific patient for a given month.
     *
     * @param $providerId
     *
     * @return mixed
     */
    public function ccmTimeBetween($providerId, array $patientIds, Carbon $monthYear = null)
    {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        return $this->repo->ccmTimeBetween($providerId, $patientIds, $monthYear)
            ->pluck('total_time', 'patient_id');
    }

    public function getChargeableServiceIdDuration(User $patient, int $duration, int $chargeableServiceId = -1): ChargeableServiceDuration
    {
        $cs = $this->getChargeableServiceById($patient, $chargeableServiceId);
        if ( ! $cs) {
            Log::warning("ActivityService::getChargeableServiceById: Could not get chargeableService for patient[$patient->id] and csId[$chargeableServiceId]");

            return new ChargeableServiceDuration(null, $duration);
        }

        return new ChargeableServiceDuration($cs->id, $duration, ChargeableService::BHI === $cs->code);
    }

    /**
     * Process activity time for month.
     *
     * @param array|int $userIds
     */
    public function processMonthlyActivityTime(
        $userIds,
        Carbon $monthYear = null
    ) {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        $monthYear = $monthYear->startOfMonth();

        if ( ! is_array($userIds)) {
            $userIds = [$userIds];
        }

        $total_time_per_user = [];
        foreach ($userIds as $userId) {
            $total_time_per_user[$userId] = 0;
        }

        $patientTotalCcmTimeMap = $this->repo->totalCCMTime($userIds, $monthYear)
            ->get()
            ->pluck('total_time', 'patient_id');

        foreach ($patientTotalCcmTimeMap as $id => $ccmTime) {
            if ($ccmTime > 0) {
                $summary = PatientMonthlySummary::firstOrCreate([
                    'patient_id' => $id,
                    'month_year' => $monthYear,
                ]);

                // this creates race conditions and inconsistencies:
                // Note with successful call is saved - 1st successful call
                // - this method is called from CallObserver.php
                // - no_of_successful_calls is 0 at this point
                // - syncCallCounts is called, which returns 1 (1 call with status reach)
                // - NotesController@store calls PatientWriteRepository@updateCallLogs which increments no_of_successful_calls from 1 to 2
                // - so 1 call, but db has 2
                /*if (0 == $summary->no_of_calls || 0 == $summary->no_of_successful_calls) {
                    $summary = $this->patientSummaryEloquentRepository->syncCallCounts($summary);
                }*/

                $total_time_per_user[$id] += $ccmTime;

                $summary->total_time = (int) $total_time_per_user[$id];
                $summary->ccm_time   = (int) $ccmTime;

                $summary->save();
            }
        }

        $patientTotalBhiTimeMap = $this->repo->totalBHITime($userIds, $monthYear)
            ->get()
            ->pluck('total_time', 'patient_id');

        foreach ($patientTotalBhiTimeMap as $id => $bhiTime) {
            if ($bhiTime > 0) {
                $summary = PatientMonthlySummary::firstOrCreate([
                    'patient_id' => $id,
                    'month_year' => $monthYear,
                ]);

                // see comment above
                /*if (0 == $summary->no_of_calls || 0 == $summary->no_of_successful_calls) {
                    $summary = $this->patientSummaryEloquentRepository->syncCallCounts($summary);
                }*/

                $total_time_per_user[$id] += $bhiTime;

                $summary->total_time = (int) $total_time_per_user[$id];
                $summary->bhi_time   = (int) $bhiTime;
                $summary->save();
            }
        }
    }

    /**
     * Get total CCM Time for a patient for a month. If no month is given, it defaults to the current month.
     *
     * @param $patientId
     *
     * @return mixed
     */
    public function totalCcmTime($patientId, Carbon $monthYear = null)
    {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        return $this->repo->totalCCMTime([$patientId], $monthYear)->pluck('total_time', 'patient_id');
    }

    public function totalTimeForChargeableServiceId(int $patientId, int $chargeableServiceId, Carbon $monthYear = null)
    {
        $cs     = ChargeableService::cached();
        $csCode = $cs->firstWhere('id', '=', $chargeableServiceId)->code;
        switch ($csCode) {
            case ChargeableService::CCM:
            case ChargeableService::CCM_PLUS_40:
            case ChargeableService::CCM_PLUS_60:
                $csFiltered = $cs
                    ->filter(fn ($cs) => in_array($cs->code, ChargeableService::CCM_CODES));
                break;
            case ChargeableService::BHI:
                $csFiltered = $cs->filter(fn ($cs) => ChargeableService::BHI === $cs->code);
                break;
            case ChargeableService::PCM:
                $csFiltered = $cs->filter(fn ($cs) => ChargeableService::PCM === $cs->code);
                break;
            case ChargeableService::RPM:
            case ChargeableService::RPM40:
            case ChargeableService::RPM60:
                $csFiltered = $cs
                    ->filter(fn ($cs) => in_array($cs->code, ChargeableService::RPM_CODES));
                break;
            case ChargeableService::GENERAL_CARE_MANAGEMENT:
                $csFiltered = $cs->filter(fn ($cs) => ChargeableService::GENERAL_CARE_MANAGEMENT === $cs->code);
                break;
            default:
                $csFiltered = collect();
                break;
        }

        return $this->repo->totalTimeForChargeableServiceIds($patientId, $csFiltered->pluck('id')->toArray(), $monthYear);
    }

    private function getChargeableServiceById(User $patient, int $id): ?ChargeableService
    {
        return LocationServices::getUsingServiceId($patient, $id, Carbon::now()->startOfMonth());
    }

    private function getChargeableServiceIdByCode(User $patient, string $code): ?int
    {
        return optional($patient
            ->primaryPractice
            ->chargeableServices
            ->where('code', '=', $code)
            ->first())
            ->id;
    }
}
