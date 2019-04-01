<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Call;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\PatientContactWindow;
use Carbon\Carbon;

trait CallAlgoHelper
{
    public function checkAdditionalNurses()
    {
        /*check for other nurses that may be available.

        Noted that this will recount the originally checked nurse,
        but since it won't return a value, it's not worth delving
        into the checks.*/

        $other_nurses = $this->patient->nursesThatCanCareforPatient();

        foreach ($other_nurses as $nurse) {
            $found = $this->checkNurseForTargetDays($nurse);

            //will exit on first match, to prevent overwriting.
            if (false != $found) {
                $this->prediction['nurse']        = $found['nurse'];
                $this->prediction['date']         = $found['date'];
                $this->prediction['window_match'] = $found['window_match'];

                return true;
            }
        }
    }

    //finds any days that have windows for patient and nurse
    //supplies $this->matchArray()
    public function checkForIntersectingDays($nurse)
    {
        $patientWindow['date']         = Carbon::parse($this->prediction['date'])->toDateString();
        $patientWindow['window_start'] = $this->prediction['window_start'];
        $patientWindow['window_end']   = $this->prediction['window_end'];

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

            $nurseWindow = $nurse->windows->first(function (
                $value,
                $key
            ) use ($dayString) {
                //check whether any days fall in this window
                return $value->date->toDateString() == $dayString;
            });

            if (null != $nurseWindow) {
                $this->matchArray[$dayString]['nurse'] = clhWindowToTimestamps(
                    $nurseWindow['date'],
                    $nurseWindow['window_time_start'],
                    $nurseWindow['window_time_end']
                );
            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

            //CHECK for patient window on target day

            $patientWindow = $patientUpcomingWindows->filter(function (
                $value,
                $key
            ) use ($day) {
                return Carbon::parse($value['window_start'])->toDateString() == $day->toDateString();
            })->first();

            if (null != $patientWindow) {
                $this->matchArray[$dayString]['patient'] = $patientWindow;
            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
        }

        return $this->matchArray;
    }

    //for every day window-pair given for nurses and patients, this will return whether they intersect.
    //supplies $this->matchArray()
    public function checkForIntersectingTimes(
        $patientWindow,
        $nurseWindow
    ) {
        $patientStartCarbon = Carbon::parse($patientWindow['window_start']);
        $patientEndCarbon   = Carbon::parse($patientWindow['window_end']);

        $nurseStartCarbon = Carbon::parse($nurseWindow['window_start'])->subMinutes(15); //padding
        $nurseEndCarbon   = Carbon::parse($nurseWindow['window_end'])->addMinutes(15); //padding

        //any overlap is true
        return ($patientStartCarbon < $nurseEndCarbon) && ($patientEndCarbon > $nurseStartCarbon);
    }

    public function checkNurseForTargetDays(Nurse $nurse)
    {
        //see if there are any window intersections within the next 3 days.
        //supplies $this->matchArray
        $date_matches = $this->checkForIntersectingDays($nurse); //first days

        foreach ($date_matches as $key => $value) {
            if (isset($value['patient'], $value['nurse'])) {
                if ($this->checkForIntersectingTimes($value['patient'], $value['nurse'])) {
                    $startWindow = Carbon::parse($value['patient']['window_start']);
                    $endWindow   = Carbon::parse($value['patient']['window_end']);

                    $match['date']         = $startWindow->toDateString();
                    $match['window_start'] = $startWindow->format('H:i');
                    $match['window_end']   = $endWindow->format('H:i');

                    $match['window_match'] = 'We found an intersecting nurse window with: '.$nurse->user->getFullName();
                    $match['nurse']        = $nurse->user_id;

                    return $match;
                }
            } else { // temp override
                $match['date']         = $this->prediction['date'];
                $match['window_start'] = $this->prediction['window_start'];
                $match['window_end']   = $this->prediction['window_end'];

                $match['window_match'] = 'No windows found, assigning to same nurse: '.$nurse->user->getFullName();
                $match['nurse']        = $nurse->user_id;

                return $match;
            }
        }

        //nurse has no windows
        return false;
    }

    //exec function for window intersection checks

    /**
     * First, we try to locate whether the last successfully reached Nurse has a window
     * If not, we move on the next Nurse who has jurisdiction.
     */
    public function findNurse()
    {
        //last contacted nurse is first up
        $this->nurse = Nurse::where('user_id', $this->patient->lastReachedNurse())
            ->with('windows')
            ->first();

        $found = $this->checkNurseForTargetDays($this->nurse);

        if (false != $found) {
            $this->prediction['nurse']        = $found['nurse'];
            $this->prediction['date']         = $found['date'];
            $this->prediction['window_match'] = $found['window_match'];

            return $this->prediction;
        }
        $found = $this->checkAdditionalNurses();

        if ( ! $found) {
            $this->prediction['nurse']        = null;
            $this->prediction['window_match'] = 'We didn\'t find a free nurse but will reassign patient name to a call soon!';
        }

        return $this->prediction;
    }

    public function formatAlgoDataForView()
    {
        //**CCM TIME**//
        $ccm_time_achieved = false;
        if ($this->ccmTime >= 1200) {
            $ccm_time_achieved = true;
        }

        $H                    = floor($this->ccmTime / 3600);
        $i                    = ($this->ccmTime / 60) % 60;
        $s                    = $this->ccmTime % 60;
        $formattedMonthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);

        $successfulCallsThisMonth = Call::numberOfSuccessfulCallsForPatientForMonth(
            $this->patient->user,
            Carbon::now()->toDateTimeString()
        );

        $this->prediction['no_of_successful_calls'] = $successfulCallsThisMonth;
        $this->prediction['ccm_time_achieved']      = $ccm_time_achieved;
        $this->prediction['formatted_monthly_time'] = $formattedMonthlyTime;
        $this->prediction['attempt_note']           = '';
    }

    public function getNextWindow()
    {
        if ('Call This Weekend' != $this->attemptNote) {
            //this will give us the first available call window from the date the logic offsets, per the patient's preferred times.
            $next_predicted_contact_window = (new PatientContactWindow())->getEarliestWindowForPatientFromDate(
                $this->patient,
                $this->nextCallDate
            );
        } else {
            //This override is to schedule calls on weekends.

            $next_predicted_contact_window['day']          = $this->nextCallDate->next(Carbon::SATURDAY)->toDateString();
            $next_predicted_contact_window['window_start'] = '10:00:00';
            $next_predicted_contact_window['window_end']   = '17:00:00';
        }

        $this->prediction = [
            'patient'      => $this->patient->user,
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
}
