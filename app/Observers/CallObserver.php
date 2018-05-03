<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 02/05/2018
 * Time: 4:05 PM
 */

namespace App\Observers;


use App\Call;
use App\Note;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class CallObserver
{
    public function saved(Call $call){

        //will isDirty work first time the model gets saved?
        if ($call->isDirty('status')){

                $patient = User::ofType('participant')
                    ->where('id', $call->inbound_cpm_id)
                    ->orWhere('id', $call->outbound_cpm_id)
                    ->first();

                $date = Carbon::parse($call->updated_at);

                $summary = PatientMonthlySummary::where('patient_id', $patient->id)
                                                ->where('month_year', $date->toDateString())
                                                ->first();

                $total = Call::where(function ($query) use ($patient) {
                    $query->where('outbound_cpm_id', $patient->id)
                        ->orWhere('inbound_cpm_id', $patient->id);
                })
                    ->where('updated_at', '>=', $date->startOfMonth())
                    ->where('updated_at', '<=', $date->endOfMonth())
                    ->whereIn('status', ['reached', 'not reached'])
                    ->get();

                $totalReached = $total->where('status', 'reached')->count();

                $summary->no_of_calls = $total->count();
                $summary->no_of_successful_calls = $totalReached;
                $summary->save();
        }

    }

}