<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

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
                $q->enrolledOrPaused();
            });
        if ($practiceId) {
            $users = $users->ofPractice($practiceId);
        } else {
            $users = $users->whereHas('practices', function ($q) {
                $q->active();
            });
        }

        return $users->whereDoesntHave('inboundCalls');
    }

    public function patientsWithoutScheduledActivities($practiceId, Carbon $afterDate = null)
    {
        return $this->patientsWithoutScheduledRelation($practiceId, 'inboundScheduledActivities', $afterDate);
    }

    public function patientsWithoutScheduledCalls($practiceId, Carbon $afterDate = null)
    {
        return $this->patientsWithoutScheduledRelation($practiceId, 'inboundScheduledCalls', $afterDate);
    }

    public function scheduledCalls()
    {
        return $this->model()->scheduled();
    }

    private function patientsWithoutScheduledRelation($practiceId, $relation, Carbon $afterDate = null)
    {
        $users = User::ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->enrolledOrPaused();
            })
            ->with([
                'patientInfo' => function ($q) {
                    return $q->select(['id', 'user_id', 'preferred_contact_language']);
                },
            ]);
        if ($practiceId) {
            $users = $users->ofPractice($practiceId);
        } else {
            $users = $users->whereHas('practices', function ($q) {
                $q->active();
            });
        }

        return $users->with('carePlan')
            ->whereDoesntHave($relation);
    }
}
