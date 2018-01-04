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
        if ( ! $month) {
            $month = Carbon::now()->startOfMonth();
        }

        return $this->model->where('status', '=', 'scheduled')
                           ->whereHas('inboundUser')
                           ->with([
                               'inboundUser.billingProvider.user'         => function ($q) {
                                   $q->select(['id', 'first_name', 'last_name', 'suffix', 'display_name']);
                               },
                               'inboundUser.notes'                        => function ($q) {
                                   $q->latest();
                               },
                               'inboundUser.patientInfo.contactWindows',
                               'inboundUser.patientInfo.monthlySummaries' => function ($q) use ($month) {
                                   $q->where('month_year', '=', $month->format('Y-m-d'));
                               },
                               'inboundUser.primaryPractice'              => function ($q) {
                                   $q->select(['id', 'display_name']);
                               },
                               'outboundUser.nurseInfo',
                               'note',
                           ]);
    }
}