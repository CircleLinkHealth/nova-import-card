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
                                                ->where('month_year', $date->startOfMonth())
                                                ->first();



            $ccmTime = $patient->getCcmTimeAttribute();

            $summary->updateMonthlyReportForPatient($patient, $ccmTime);

        }

    }

}