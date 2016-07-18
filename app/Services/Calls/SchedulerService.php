<?php namespace App\Services\Calls;


use App\PatientInfo;
use Carbon\Carbon;

class SchedulerService
{
//    protected $patient;
//
//    public function __construct($user_patient)
//    {
//        $this->patient = $user_patient;
//    }

    //This function will be the initial rendition of algorithm
    //to calculate the patient's next call date.
    //It will take the patient's User object
    //as input and return a datetime

    // proposed layers to filter predicted date and time:
        // 1) Check the patient's preferred days and times

    public function scheduleCall($patient, $success){

        if($success){

            return $this->getPatientNextCallWindow($patient);

        } else {
            //Attempt reschedule

            return $this->scheduleNextAttempt($patient);

        }
    }

    public function getPatientNextCallWindow($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = $patient_preferred_times['window_start'];
        $window_end = $patient_preferred_times['window_end']; // @todo: consider usage once algorithm is finer
        $dates = $patient_preferred_times['days'];

        $earliest_contact_day = min($dates);

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

    public function scheduleNextAttempt($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $window_start = $patient_preferred_times['window_start'];
        $window_end = $patient_preferred_times['window_end']; // @todo: consider usage once algorithm is finer
        $dates = $patient_preferred_times['days'];

        $earliest_contact_day = min($dates);

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            //give it the start time for now...
            'raw_time' => $window_start,
            'successful' => false

        ];
    }


}