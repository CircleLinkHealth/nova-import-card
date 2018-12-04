<?php

namespace App\Repositories\Eloquent;

use App\Activity;
use Carbon\Carbon;

class ActivityRepository
{
    /**
     * Get the CCM Time provided by a specific provider to a specific patient for a given month.
     *
     * @param int $providerId
     * @param array $patientIds
     * @param Carbon|null $monthYear
     *
     * @return mixed
     */
    public function ccmTimeBetween(int $providerId, array $patientIds, Carbon $monthYear = null)
    {
        return $this->totalCCMTime($patientIds, $monthYear)
                    ->where('provider_id', '=', $providerId);
    }

    /**
     * Get the total CCM time for the given patients for a given month.
     *
     * @param array $userIds
     * @param Carbon $monthYear
     *
     * @return $this
     */
    public function totalCCMTime(array $userIds, Carbon $monthYear)
    {
        return Activity::selectRaw('sum(duration) as total_time, patient_id')
                       ->where('is_behavioral', 0)
                       ->whereIn('patient_id', $userIds)
                       ->where('performed_at', '>=', $monthYear->startOfMonth())
                       ->where('performed_at', '<=', $monthYear->copy()->endOfMonth())
                       ->groupBy('patient_id');
    }

    /**
     * Get the total BHI time for the given patients for a given month.
     *
     * @param array $userIds
     * @param Carbon $monthYear
     *
     * @return $this
     */
    public function totalBHITime(array $userIds, Carbon $monthYear)
    {
        return Activity::selectRaw('sum(duration) as total_time, patient_id')
                       ->where('is_behavioral', 1)
                       ->whereIn('patient_id', $userIds)
                       ->where('performed_at', '>=', $monthYear->startOfMonth())
                       ->where('performed_at', '<=', $monthYear->copy()->endOfMonth())
                       ->groupBy('patient_id');
    }
}
