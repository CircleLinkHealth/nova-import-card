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
use App\User;
use Carbon\Carbon;

class CallObserver
{
    public function saved(Call $call)
    {
        if ($call->isDirty('status')) {

            $patient = User::ofType('participant')
                           ->where('id', $call->inbound_cpm_id)
                           ->orWhere('id', $call->outbound_cpm_id)
                           ->first();

            $date = Carbon::parse($call->updated_at);

            $summary = PatientMonthlySummary::where('patient_id', $patient->id)
                                            ->where('month_year', $date->startOfMonth())
                                            ->first();


            $ccmTime = $patient->getCcmTimeAttribute();

            $day_start = $date->copy()->startOfMonth();
            $day_end   = $date->copy()->endOfMonth();

            $no_of_calls = Call::where(function ($q) use ($patient) {
                $q->where('outbound_cpm_id', $patient->id)
                  ->orWhere('inbound_cpm_id', $patient->id);
            })
                               ->where('called_date', '>=', $day_start)
                               ->where('called_date', '<=', $day_end)
                               ->whereIn('status', ['reached', 'not reached'])
                               ->get();

            $no_of_successful_calls = $no_of_calls->where('status', 'reached')->count();

            if ($summary) {
                $summary->ccm_time               = $ccmTime;
                $summary->no_of_calls            = $no_of_calls->count();
                $summary->no_of_successful_calls = $no_of_successful_calls;
                $summary->save();
            } else {
                //dd('no report');
            }

        }

    }

}