<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/22/2017
 * Time: 4:22 PM
 */

namespace App\Repositories\Eloquent;


use App\Activity;
use Carbon\Carbon;

class ActivityRepository
{
    /**
     * @param array $userIds
     * @param Carbon $monthYear
     *
     * @return $this
     */
    public function totalCCMTime(array $userIds, Carbon $monthYear)
    {
        return Activity::selectRaw('sum(duration) as total_time, patient_id')
                       ->whereIn('patient_id', $userIds)
                       ->where('performed_at', '>=', $monthYear->startOfMonth())
                       ->where('performed_at', '<=', $monthYear->copy()->endOfMonth());
    }

    public function ccmTimeBetween(int $providerId, array $patientIds, Carbon $monthYear = null) {
        return $this->totalCCMTime($patientIds, $monthYear)
            ->where('provider_id', '=', $providerId);
    }
}