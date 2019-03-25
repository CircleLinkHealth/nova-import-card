<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use App\Repositories\CallRepository;
use App\Repositories\Eloquent\ActivityRepository;
use App\Repositories\PatientSummaryEloquentRepository;
use Carbon\Carbon;

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
     * @param array       $patientIds
     * @param Carbon|null $monthYear
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

    /**
     * Process activity time for month.
     *
     * @param array|int   $userIds
     * @param Carbon|null $monthYear
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

        $acts = $this->repo->totalCCMTime($userIds, $monthYear)
            ->get()
            ->pluck('total_time', 'patient_id');

        //add 0 for the ones not found in this monthYear
        foreach ($userIds as $userId) {
            if ( ! isset($acts[$userId])) {
                $acts[$userId] = 0;
            }
        }

        foreach ($acts as $id => $ccmTime) {
            if ($ccmTime > 0) {
                $summary = PatientMonthlySummary::updateOrCreate([
                    'patient_id' => $id,
                    'month_year' => $monthYear,
                ], [
                    'ccm_time' => $ccmTime,
                ]);

                if (0 == $summary->no_of_calls || 0 == $summary->no_of_successful_calls) {
                    $summary = $this->patientSummaryEloquentRepository->syncCallCounts($summary);
                }

                $total_time_per_user[$id] += $ccmTime;

                $summary->total_time = (int) $total_time_per_user[$id];
                $summary->save();
            }
        }

        $bhi_acts = $this->repo->totalBHITime($userIds, $monthYear)
            ->get()
            ->pluck('total_time', 'patient_id');

        //add 0 for the ones not found in this monthYear
        foreach ($userIds as $userId) {
            if ( ! isset($bhi_acts[$userId])) {
                $bhi_acts[$userId] = 0;
            }
        }

        foreach ($bhi_acts as $id => $bhiTime) {
            if ($bhiTime > 0) {
                $summary = PatientMonthlySummary::updateOrCreate([
                    'patient_id' => $id,
                    'month_year' => $monthYear,
                ], [
                    'bhi_time' => $bhiTime,
                ]);

                if (0 == $summary->no_of_calls || 0 == $summary->no_of_successful_calls) {
                    $summary = $this->patientSummaryEloquentRepository->syncCallCounts($summary);
                }

                $total_time_per_user[$id] += $bhiTime;

                $summary->total_time = (int) $total_time_per_user[$id];
                $summary->save();
            }
        }
    }

    /**
     * Get total CCM Time for a patient for a month. If no month is given, it defaults to the current month.
     *
     * @param $patientId
     * @param Carbon|null $monthYear
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
}
