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

    public function scheduleCall($patient){
        
        return $this->getPatientNextCallWindow($patient);

    }

    public function getPatientNextCallWindow($patient){

        $patient_preferred_times = (new PatientInfo)->getPatientPreferredTimes($patient);

        $time = $patient_preferred_times['time'];
        $dates = $patient_preferred_times['days'];

        $earliest_contact_day = min($dates);

        $window = (new PatientInfo)->parsePatientCallPreferredWindow($patient);

        return [
            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            'raw_time' => $time

        ];
    }


}