<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 02/05/2018
 * Time: 4:05 PM
 */

namespace App\Observers;


use App\Call;
use App\PatientMonthlySummary;
use App\Services\ActivityService;
use App\User;
use Carbon\Carbon;

class CallObserver
{
    /**
     * @var ActivityService
     */
    private $activityService;

    public function __construct(ActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    public function saved(Call $call)
    {
        if ($call->isDirty('status')) {

            $patient = User::ofType('participant')
                           ->where('id', $call->inbound_cpm_id)
                           ->orWhere('id', $call->outbound_cpm_id)
                           ->first();

            $date = Carbon::parse($call->updated_at);

            $this->activityService->processMonthlyActivityTime($patient->id, $date);

            $start = $date->copy()->startOfMonth();
            $end   = $date->copy()->endOfMonth();

            $no_of_calls = Call::where(function ($q) {
                $q->whereNull('type')
                  ->orWhere('type', '=', 'call')
                  ->orWhere('sub_type', '=', 'call_back')
                  ->orWhere('sub_type', '=', 'call');
            })
                               ->where(function ($q) use ($patient) {
                                   $q->where('outbound_cpm_id', $patient->id)
                                     ->orWhere('inbound_cpm_id', $patient->id);
                               })
                               ->where('called_date', '>=', $start)
                               ->where('called_date', '<=', $end)
                               ->whereIn('status', ['reached', 'not reached'])
                               ->get();

            $no_of_successful_calls = $no_of_calls->where('status', 'reached')->count();

            $summary = PatientMonthlySummary::where('patient_id', $patient->id)
                                            ->where('month_year', $date->startOfMonth())
                                            ->update([
                                                'no_of_calls'            => $no_of_calls->count(),
                                                'no_of_successful_calls' => $no_of_successful_calls,
                                            ]);
        }

    }

}