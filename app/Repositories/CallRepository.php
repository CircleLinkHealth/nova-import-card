<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/11/2017
 * Time: 2:58 PM
 */

namespace App\Repositories;


use App\Call;
use App\User;
use Carbon\Carbon;

class CallRepository
{
    private $model;

    public function __construct(Call $model)
    {
        $this->model = $model;
    }

    /**
     * Get the number of calls for a patient for a month
     *
     * @param $patientUserId
     * @param Carbon|null $monthYear
     *
     * @return mixed
     */
    public function numberOfCalls($patientUserId, Carbon $monthYear = null)
    {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        return $this->model
            ->where([
                ['inbound_cpm_id', '=', $patientUserId],
                ['status', '!=', 'scheduled'],
            ])
            ->ofMonth($monthYear)
            ->count();
    }

    /**
     * Get the number of successful calls for a patient for a month
     *
     * @param $patientUserId
     * @param Carbon|null $monthYear
     *
     * @return mixed
     */
    public function numberOfSuccessfulCalls($patientUserId, Carbon $monthYear = null)
    {
        if ( ! $monthYear) {
            $monthYear = Carbon::now();
        }

        return $this->model
            ->where([
                ['inbound_cpm_id', '=', $patientUserId],
            ])
            ->ofStatus('reached')
            ->ofMonth($monthYear)
            ->count();
    }

    public function patientsWithoutScheduledCalls($practiceId, Carbon $start)
    {
        return User::ofType('participant')
                   ->ofPractice($practiceId)
                   ->whereDoesntHave('inboundScheduledCalls');
    }

    public function scheduledCalls(Carbon $month = null)
    {
        return $this->model->scheduled($month);
    }
}