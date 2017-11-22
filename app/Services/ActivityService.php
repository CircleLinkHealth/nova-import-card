<?php namespace App\Services;

use App\Activity;
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
     * @param array $userIds
     * @param Carbon|null $monthYear
     */
    public function processMonthlyActivityTime(
        array $userIds,
        Carbon $monthYear = null
    ) {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        $acts = $this->repo->totalCCMTime($userIds, $monthYear)
                        ->pluck('total_time', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            $info = Patient::updateOrCreate([
                'user_id' => $id,
            ], [
                'cur_month_activity_time' => $ccmTime,
            ]);

            (new PatientMonthlySummary())->updateCCMInfoForPatient($info, $ccmTime);
        }
    }

    public function ccmTimeBetween($providerId, array $patientIds, Carbon $monthYear = null)
    {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        return $this->repo->ccmTimeBetween($providerId, $patientIds, $monthYear)
            ->pluck('total_time', 'patient_id');
    }
}
