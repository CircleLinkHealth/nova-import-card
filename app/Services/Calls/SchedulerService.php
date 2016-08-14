<?php namespace App\Services\Calls;


use App\Algorithms\Calls\PredictCall;
use App\Call;
use App\Note;
use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class SchedulerService
{

    protected $callPredictor;

    //Instantiate class with a prediction algorithm of choice
    public function __construct(PredictCall $algorithm)
    {
        $this->callPredictor = $algorithm;
    }

    // Success is the call's status.
    // true for reached, false for not reached 
    public function getNextCall($patient, $note ,$success)
    {

        $scheduled_call = $this->getScheduledCallForPatient($patient);

        $note = Note::find($note);

        //Updates Call Record
        PatientMonthlySummary::updateCallInfoForPatient($patient->patientInfo, $success);


        if ($success) {

            return $this->callPredictor->successfulCallHandler($patient, $note, $scheduled_call);

        } else {

            return $this->callPredictor->unsuccessfulCallHandler($patient, $note, $scheduled_call);

        }
    }
    
    //Create new scheduled call
    public function storeScheduledCall($patientId, $window_start, $window_end, $date)
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

}