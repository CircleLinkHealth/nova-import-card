<?php namespace App\Services;

use App\Activity;
use App\Patient;
use App\PatientMonthlySummary;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

class ActivityService
{
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

        $acts = Activity::whereIn('patient_id', $userIds)
                            ->where('performed_at', '>=', $monthYear->startOfMonth())
                            ->where('performed_at', '<=', $monthYear->copy()->endOfMonth())
                            ->selectRaw('sum(duration) as total_duration, patient_id')
                            ->pluck('total_duration', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            $info = Patient::updateOrCreate([
                'user_id' => $id,
            ], [
                'cur_month_activity_time' => $ccmTime
            ]);

            (new PatientMonthlySummary())->updateCCMInfoForPatient($info, $ccmTime);
        }
    }
}
