<?php namespace App\Services\Calls;


use App\Algorithms\Calls\PredictCall;
use App\Call;
use App\Note;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class SchedulerService
{

    // Success is the call's status.
    // true for reached, false for not reached 
    public function getNextCall($patient, $noteId ,$success)
    {

        $scheduled_call = $this->getScheduledCallForPatient($patient);

        $note = Note::find($noteId);

        //Updates Call Record
        PatientMonthlySummary::updateCallInfoForPatient($patient->patientInfo, $success);

        return (new PredictCall($patient, $scheduled_call, $success))->predict($note);

    }
    
    //Create new scheduled call
    public function storeScheduledCall($patientId, $window_start, $window_end, $date, $nurse_id = false)
    {

        $patient = User::find($patientId);

        $window_start = Carbon::parse($window_start)->format('H:i');
        $window_end = Carbon::parse($window_end)->format('H:i');

        return Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'inbound_phone_number' => $patient->phone ? $patient->phone : '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $patient->ID,
            'outbound_cpm_id' => isset($nurse_id) ? $nurse_id : '',

            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'call_date' => $date,
            'window_start' => $window_start,
            'window_end' => $window_end,

            'is_cpm_outbound' => true

        ]);
    }

    //extract the last scheduled call
    public function getScheduledCallForPatient($patient){

        $call = Call::where(function($q) use ($patient)
        {
            $q->where('outbound_cpm_id', $patient->ID)
            ->orWhere('inbound_cpm_id', $patient->ID);
        })
            ->where('status', '=' , 'scheduled')
            ->first();

        return $call;
    }

    public static function getUnAttemptedCalls(){

        $calls = Call::whereStatus('scheduled')
            ->where('call_date','<=', Carbon::now()->toDateString())->get();

        $missed = array();

        /*
         * Check to see if the call is dropped if it's the current day
         * Since we store the date and times separately for other
         * considerations, we have to join them and compare
         * to see if a call was missed on the same day
        */

        foreach ($calls as $call){

            $end_carbon = Carbon::parse($call->call_date);

            $carbon_hour_end = Carbon::parse($call->window_end)->format('H');
            $carbon_minutes_end = Carbon::parse($call->window_end)->format('i');

            $end_time = $end_carbon->setTime($carbon_hour_end, $carbon_minutes_end)->toDateTimeString();

            $now_carbon = Carbon::now()->toDateTimeString();

            if($end_time < $now_carbon){
                $missed[] = $call;
            }

        }

        return $missed;

    }

}