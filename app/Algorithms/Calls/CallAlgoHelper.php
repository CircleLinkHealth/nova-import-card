<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 2:04 PM
 */

namespace App\Algorithms\Calls;


use App\Call;
use App\PatientContactWindow;
use Carbon\Carbon;

trait CallAlgoHelper
{

    public function formatAlgoDataForView($prediction)
    {

        //**CCM TIME**//
        $ccm_time_achieved = false;
        if ($this->ccmTime >= 1200) {
            $ccm_time_achieved = true;
        }

        $H = floor($this->ccmTime / 3600);
        $i = ($this->ccmTime / 60) % 60;
        $s = $this->ccmTime % 60;
        $formattedMonthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);

        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient->user, Carbon::now()->toDateTimeString());

        $prediction['no_of_successful_calls'] = $successfulCallsThisMonth;
        $prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $prediction['formatted_monthly_time'] = $formattedMonthlyTime;
        $prediction['attempt_note'] = '';

        return $prediction;
    }

    public function getNextWindow(){

        //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
        $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient,
                                                                                                         $this->nextCallDate);

        $prediction = [
            'patient' => $this->patient,
            'date' => $next_predicted_contact_window['day'],
            'window_start' => $next_predicted_contact_window['window_start'],
            'window_end' => $next_predicted_contact_window['window_end'],
            'logic' => $this->logic,
            'attempt_note' => $this->attemptNote
        ];

        //Add some more view stuff to array
        $prediction = $this->formatAlgoDataForView($prediction);

        return $prediction;
    }

}