<?php namespace App\Services\Calls;


use App\Call;
use App\PatientInfo;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SchedulerService
{

    //This function will be the initial rendition of algorithm
    //to calculate the patient's next call date.
    //It will take the patient's User object
    //as input and return a datetime

    // proposed layers to filter predicted date and time:
    // 1) Check the patient's preferred days and times

    public function predictCall($patient, $note ,$success)
    {

        $scheduled_call = $this->getCallForPatient($patient);

        if ($success) {

            //Update and close previous call

            if($scheduled_call) {
                $scheduled_call->status = 'reached';
                $scheduled_call->note_id = $note;
                $scheduled_call->call_date = Carbon::now()->format('Y-m-d');
                $scheduled_call->outbound_cpm_id = Auth::user()->ID;
            }

            return $this->successfulCallHandler($patient);

        } else {

            //Update and close previous call, if exists.
            if($scheduled_call) {
                $scheduled_call->status = 'not reached';
                $scheduled_call->note_id = $note;
                $scheduled_call->call_date = Carbon::now()->format('Y-m-d');
                $scheduled_call->outbound_cpm_id = Auth::user()->ID;
            }

            return $this->unsuccessfulCallHandler($patient);

        }
    }

    public function successfulCallHandler($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = Carbon::parse($patient_preferred_times['window_start'])->format('H:i');
        $window_end = Carbon::parse($patient_preferred_times['window_end'])->format('H:i');

        $dates = $patient_preferred_times['days'];

        //TO CALCULATE
        $earliest_contact_day = Carbon::parse(min($dates))->addWeek()->format('Y-m-d');

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

//      $this->storeScheduledCall($patient->ID, $window_start, $window_end, $earliest_contact_day);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => true
        ];
    }

    //Unsuccessful Call Handler

    public function unsuccessfulCallHandler($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = Carbon::parse($patient_preferred_times['window_start'])->format('H:i:s');
        $window_end = Carbon::parse($patient_preferred_times['window_end'])->format('H:i:s');

        $earliest_contact_day = Carbon::now()->addDay()->format('Y-m-d');

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

//      $this->storeScheduledCall($patient->ID, $window_start, $window_end, $earliest_contact_day);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => false
        ];
    }

    //This is the function where the new call is created.
    
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

    public function getCallForPatient($patient){

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