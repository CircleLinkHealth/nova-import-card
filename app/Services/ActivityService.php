<?php namespace App\Services;

use App\Patient;
use App\PatientMonthlySummary;
use App\Repositories\Eloquent\ActivityRepository;
use Carbon\Carbon;

class ActivityService
{
    protected $repo;

    public function __construct(ActivityRepository $repo)
    {
        $this->repo = $repo;
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

        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $acts = $this->repo->totalCCMTime($userIds, $monthYear)
                           ->pluck('total_time', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            $info = Patient::updateOrCreate([
                'user_id' => $id,
            ], [
                'cur_month_activity_time' => $ccmTime,
            ]);

            (new PatientMonthlySummary())->updateCCMInfoForPatient($id, $ccmTime);
        }
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
