<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Call;
use App\User;
use Carbon\Carbon;

class CallRepository
{
    public function call($id)
    {
        return $this->model()->findOrFail($id);
    }

    public function count()
    {
        return $this->model()->select('id', DB::raw('count(*) as total'))->count();
    }

    public function model()
    {
        return app(Call::class);
    }

    /**
     * Get the number of calls for a patient for a month.
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
     * Get the number of successful calls for a patient for a month.
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

    public function patientsWithoutAnyInboundCalls($practiceId)
    {
        $users = User::ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->enrolled();
            });
        if ($practiceId) {
            $users = $users->ofPractice($practiceId);
        }

        return $users->whereDoesntHave('inboundCalls');
    }

    public function patientsWithoutScheduledCalls($practiceId, Carbon $afterDate = null)
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
}
