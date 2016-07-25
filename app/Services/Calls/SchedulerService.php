<?php namespace App\Services\Calls;


use App\Call;
use App\PatientInfo;
use Carbon\Carbon;

class SchedulerService
{

    //This function will be the initial rendition of algorithm
    //to calculate the patient's next call date.
    //It will take the patient's User object
    //as input and return a datetime

    // proposed layers to filter predicted date and time:
    // 1) Check the patient's preferred days and times

    public function predictCall($patient, $success)
    {
        if ($success) {

            return $this->predictNextCall($patient);

        } else {

            return $this->predictNextAttempt($patient);

        }
    }
    
    public function analyzePatientCCMProgress($patient){
        
//        $ccm_time = $patient
        
        
        
    }

    public function predictNextCall($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = $patient_preferred_times['window_start'];

//        $window_end = $patient_preferred_times['window_end']; // @todo: consider usage once algorithm is finer
        $dates = $patient_preferred_times['days'];

        $earliest_contact_day = Carbon::parse(min($dates))->addWeek()->format('Y-m-d');

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => true
        ];
    }

    public function predictNextAttempt($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = Carbon::parse($patient_preferred_times['window_start'])->addWeek()->format('H:i:s');;

        $earliest_contact_day = Carbon::now()->addDay()->format('Y-m-d');

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        $this->storeScheduledCall($patient->ID, $window_start, $earliest_contact_day);


        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => false
        ];
    }
    
    public function storeScheduledCall($patientId, $window_start, $date)
    {

        $window_end = Carbon::parse($window_start)->addHour()->format('H:i:s');

        return Call::create([

            'service' => 'phone',
            'status' => 'scheduled',

            'inbound_phone_number' => '',
            'outbound_phone_number' => '',

            'inbound_cpm_id' => $patientId,

            'call_time' => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            'call_date' => $date,
            'window_start' => $window_start,
            'window_end' => $window_end,

            'is_cpm_outbound' => true

        ]);
    }
}