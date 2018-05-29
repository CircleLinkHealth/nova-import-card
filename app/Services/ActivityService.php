<?php namespace App\Services;

use App\Patient;
use App\PatientMonthlySummary;
use App\Repositories\CallRepository;
use App\Repositories\Eloquent\ActivityRepository;
use Carbon\Carbon;

class ActivityService
{
    protected $callRepo;
    protected $repo;

    public function __construct(ActivityRepository $repo, CallRepository $callRepo)
    {
        $this->repo     = $repo;
        $this->callRepo = $callRepo;
    }

    /**
     * Process activity time for month
     *
     * @param array|int $userIds
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

        $total_time = 0;

        $acts = $this->repo->totalCCMTime($userIds, $monthYear)
                           ->get()
                           ->pluck('total_time', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            $summary = PatientMonthlySummary::updateOrCreate([
                'patient_id' => $id,
                'month_year' => $monthYear->toDateString(),
            ], [
                'ccm_time' => $ccmTime,
            ]);

            if ($summary->no_of_calls == 0 && $summary->no_of_successful_calls == 0) {
                $summary->no_of_calls            = $this->callRepo->numberOfCalls($id, $monthYear);
                $summary->no_of_successful_calls = $this->callRepo->numberOfSuccessfulCalls($id, $monthYear);
                $summary->save();
            }

            $total_time += $ccmTime;
        }

        $bhi_acts = $this->repo->totalBHITime($userIds, $monthYear)
                               ->get()
                               ->pluck('total_time', 'patient_id');

        foreach ($bhi_acts as $id => $bhiTime) {
            $summary = PatientMonthlySummary::updateOrCreate([
                'patient_id' => $id,
                'month_year' => $monthYear->toDateString(),
            ], [
                'bhi_time' => $bhiTime,
            ]);

            if ($summary->no_of_calls == 0 && $summary->no_of_successful_calls == 0) {
                $summary->no_of_calls            = $this->callRepo->numberOfCalls($id, $monthYear);
                $summary->no_of_successful_calls = $this->callRepo->numberOfSuccessfulCalls($id, $monthYear);
                $summary->save();
            }

            $total_time += $bhiTime;

            if ($monthYear->toDateString() == Carbon::now()->startOfMonth()->toDateString()) {
                $info = Patient::updateOrCreate([
                    'user_id' => $id,
                ], [
                    'cur_month_activity_time' => (int)$total_time,
                ]);
            }
        }


        $summary->total_time = (int)$total_time;
        $summary->save();
    }

    /**
     * Get the CCM Time provided by a specific provider to a specific patient for a given month.
     *
     * @param $providerId
     * @param array $patientIds
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
