<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 9/17/16
 * Time: 2:04 PM
 */

namespace App\Algorithms\Calls;


use App\Call;
use App\NurseInfo;
use App\PatientContactWindow;
use Carbon\Carbon;

trait CallAlgoHelper
{

    public function getNextWindow()
    {

        if ($this->attemptNote != 'Call This Weekend') {

            //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
            $next_predicted_contact_window = (new PatientContactWindow)->getEarliestWindowForPatientFromDate($this->patient,
                $this->nextCallDate);
            
        } else {

            //This override is to schedule calls on weekends.

            $next_predicted_contact_window['day'] = $this->nextCallDate->next(Carbon::SATURDAY)->toDateString();
            $next_predicted_contact_window['window_start'] = '10:00:00';
            $next_predicted_contact_window['window_end'] = '17:00:00';

        }

        $this->prediction = [
            'patient'      => $this->patient,
            'date'         => $next_predicted_contact_window['day'],
            'window_start' => $next_predicted_contact_window['window_start'],
            'window_end'   => $next_predicted_contact_window['window_end'],
            'logic'        => $this->logic,
            'attempt_note' => $this->attemptNote,
        ];

        //Add some more view stuff to array
        $this->formatAlgoDataForView();

        return $this->prediction;
    }

    public function formatAlgoDataForView()
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

        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth($this->patient->user,
            Carbon::now()->toDateTimeString());

        $this->prediction['no_of_successful_calls'] = $successfulCallsThisMonth;
        $this->prediction['ccm_time_achieved'] = $ccm_time_achieved;
        $this->prediction['formatted_monthly_time'] = $formattedMonthlyTime;
        $this->prediction['attempt_note'] = '';

    }

    //exec function for window intersection checks

    public function intersectWithNurseWindows()
    {

        //last contacted nurse is first up
        $this->nurse = NurseInfo::where('user_id', $this->patient->lastReachedNurse())
            ->with('windows')
            ->first();

        //see if there are any window intersections within the next 3 days.
        //supplies $this->matchArray

         $this->checkForIntersectingDays($this->nurse); //first days
         $this->checkForIntersectingTimes(); //then whether they have intersecting times

        $adjustment = collect($this->matchArray)->first();

            if($adjustment && isset($adjustment['intersects'])){

                $startWindow = Carbon::parse($adjustment['patient']['window_start']);
                $endWindow = Carbon::parse($adjustment['patient']['window_end']);

                $this->prediction['date'] = $startWindow->toDateString();
                $this->prediction['window_start'] = $startWindow->format('H:i');
                $this->prediction['window_end'] = $endWindow->format('H:i');

                $this->prediction['logic'] .= '. We also found an intersecting nurse window!';
                $this->prediction['nurse'] = $this->nurse->user_id;


            } else {

                $this->prediction['logic'] .= '. We didn\'t find a nurse window... (note, currently only supports last contacted nurse)';
                $this->prediction['nurse'] = null;

            }

    }

    //finds any days that have windows for patient and nurse
    //supplies $this->matchArray()
    public function checkForIntersectingDays($nurse){

        $patientWindow['date'] = Carbon::parse($this->prediction['date'])->toDateString();
        $patientWindow['window_start'] = $this->prediction['window_start'];
        $patientWindow['window_end'] = $this->prediction['window_end'];

        $targetDays = [

            Carbon::parse($patientWindow['date']),
            Carbon::parse($patientWindow['date'])->addDays(1),
            Carbon::parse($patientWindow['date'])->addDays(2),

        ];

        $patientUpcomingWindows = PatientContactWindow::getNextWindowsForPatientFromDate($this->patient, $patientWindow['date']);

        foreach ($targetDays as $day) {

            $dayString = $day->toDateString();

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//


            //CHECK for nurse window on target day

            $nurseWindow = $nurse->windows->first(function ($value, $key) use ($day) {

                //check whether any days fall in this window
                return $value->date->toDateString() == $day->toDateString();

            });

            if($nurseWindow != null) {

                $this->matchArray[$dayString]['nurse'] = clhWindowToTimestamps($nurseWindow['date'],
                                                                          $nurseWindow['window_time_start'],
                                                                          $nurseWindow['window_time_end']);
            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

            //CHECK for patient window on target day

            $patientWindow = $patientUpcomingWindows->filter(function ($value, $key) use ($day) {

                return Carbon::parse($value['window_start'])->toDateString() == $day->toDateString();

            })->first();

            if($nurseWindow != null) {

                $this->matchArray[$dayString]['patient'] = $patientWindow;

            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

        }

    }

    //for every day window-pair given for nurses and patients, this will return whether they intersect.
    //supplies $this->matchArray()
    public function checkForIntersectingTimes(){

        foreach ($this->matchArray as $day) {

            $patientStartCarbon = Carbon::parse($day['patient']['window_start']);
            $patientEndCarbon = Carbon::parse($day['patient']['window_end']);

            $nurseStartCarbon = Carbon::parse($day['nurse']['window_start'])->subMinutes(15); //padding
            $nurseEndCarbon = Carbon::parse($day['nurse']['window_end'])->subMinutes(15); //padding

            $timeBorder1 = $nurseStartCarbon->between($patientStartCarbon, $patientEndCarbon);
            $timeBorder2 = $nurseEndCarbon->between($patientStartCarbon, $patientEndCarbon);


            $this->matchArray[$patientStartCarbon->toDateString()]['intersects'] = $timeBorder1 || $timeBorder2;

        }

        return $this->matchArray;

    }

}