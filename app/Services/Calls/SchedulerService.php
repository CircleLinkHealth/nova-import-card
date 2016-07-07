<?php namespace App\Services\Calls;


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

    public function scheduleCall($patient){

        $time = Carbon::parse($patient->patientInfo->preferred_contact_time)->format('H:i');

        $days = PatientInfo::numberToTextDaySwitcher($patient->patientInfo->preferred_cc_contact_days);
        $days = $days = explode(',', $days);
        $days_formatted = array();


        foreach ($days as $day){
            $days_formatted[] = Carbon::parse($day)->format('Y-m-d');
        }

        $window_date_time_930am = Carbon::parse('09:30')->format('H:i');
        $window_date_time_12n = Carbon::parse('12:00')->format('H:i');
        $window_date_time_3pm = Carbon::parse('15:00')->format('H:i');
        $window_date_time_6pm = Carbon::parse('18:00')->format('H:i');


        $earliest_contact_day = min($days_formatted);
        $window = '';

        switch ($time){
            case ($time >= $window_date_time_930am && $time < $window_date_time_12n):
                $window = PatientInfo::CALL_WINDOW_0930_1200; break;
            case ($time >= $window_date_time_12n && $time < $window_date_time_3pm):
                $window = PatientInfo::CALL_WINDOW_1200_1500; break;
            case ($time >= $window_date_time_3pm && $time > $window_date_time_6pm):
                $window = PatientInfo::CALL_WINDOW_1500_1800; break;
            default:
                $window = 'Not able to calculate suitable window'; break;
        }

        return [

            'patient' => $patient,
            'date' => $earliest_contact_day,
            'window' => $window,
            'raw_time' => $time

        ];
        
        
    }


}