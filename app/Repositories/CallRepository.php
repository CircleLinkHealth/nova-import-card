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
    public function model()
    {
        return app(Call::class);
    }

    public function count()
    {
        return $this->model()->select('id', DB::raw('count(*) as total'))->count();
    }

    public function call($id)
    {
        return $this->model()->findOrFail($id);
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

        return $this->model()
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

        return $this->model()
                    ->where([
                        ['inbound_cpm_id', '=', $patientUserId],
                    ])
                    ->ofStatus('reached')
                    ->ofMonth($monthYear)
                    ->count();
    }

    public function patientsWithoutScheduledCalls($practiceId, Carbon $afterDate)
    {
        $users = User::ofType('participant');
        if ($practiceId) {
            $users = $users->ofPractice($practiceId);
        }

        return $users->with('carePlan')
                     ->whereHas('patientInfo', function ($q) {
                         $q->enrolled();
                     })
                     ->whereDoesntHave('inboundScheduledCalls');
    }

    public function scheduledCalls()
    {
        return $this->model()->scheduled();
    }

    public function patientsWithoutAnyInboundCalls($practiceId)
    {
        $users = User::ofType('participant');
        if ($practiceId) {
            $users = $users->ofPractice($practiceId);
        }
        return $users->whereDoesntHave('inboundCalls');
    }
}