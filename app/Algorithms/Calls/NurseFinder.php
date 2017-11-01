<?php
/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 2/13/17
 * Time: 11:23 AM
 */

namespace App\Algorithms\Calls;

use App\Call;
use App\Nurse;
use App\NurseContactWindow;
use App\Patient;
use App\PatientContactWindow;
use App\User;
use Carbon\Carbon;

class NurseFinder
{

    /***
     * Locates an RN for a given patient
     * Use function handle to get (Nurse, target date, start window and end window)
     */

    protected $patient;
    protected $nursesForPatient;

    protected $offsetDate;
    protected $windowStart;
    protected $windowEnd;
    protected $matchArray;
    protected $previousCall;


    protected $data;

    public function __construct(
        Patient $patient,
        Carbon $date,
        $windowStart,
        $windowEnd,
        $previousCall
    ) {

        $this->patient = $patient;
        $this->offsetDate = $date;
        $this->windowStart = $windowStart;
        $this->windowEnd = $windowEnd;
        $this->nursesForPatient = $this->getMostFrequentNursesForPatient();
        $this->previousCall = $previousCall;
    }

    public function find()
    {

//        foreach ($this->nursesForPatient as $nurseId => $count) {
//
//            $nurse = Nurse::where('user_id', $nurseId)->first();
//
//            $date_match = $this->checkNurseForTargetDays($nurse); //first days
//
//            if ($date_match) {
//
//                $no_of_calls_in_window = $this->countScheduledCallCountForNurseForWindow($date_match);
//
//                //check threshold for nurse
//                if ($no_of_calls_in_window < 6) {
//
//                    return $date_match;
//
//                }// else keep looking!
//
//            }
//
//        }
//        $match['nurse'] = current(array_keys($this->nursesForPatient));

        if ($this->previousCall['attempt_note'] == '') {
            $match['nurse'] = $this->previousCall['outbound_cpm_id'];
            $match['window_match'] = 'Attempt Note was empty, assigning to care person that last contacted patient. ';
        } else {
            $data = $this->getLastRNCallWithoutAttemptNote($this->patient, $this->previousCall['outbound_cpm_id']);
            $match['window_match'] = 'Attempt Note present, looking for last care person that contacted patient without one..';

            if ($data == null) {
                //assign back to RN that first called patient
                $match['nurse'] = $this->previousCall['outbound_cpm_id'];
                $match['window_match'] .= " No previous care person without attempt note found, assigning to last contacted care person. ";
            } else {
                $match['nurse'] = $data;
                $match['window_match'] .= " Found care person that contacted patient in the past without attempt note. ";
            }
        }

        /*
         *
        Always schedule next call to RN who made last call EXCEPT:
        if attempt note exists for a call: then assign next call to RN who performed the last call without an attempt note
        note: if there is no such RN (ex: first call ever has an attempt note), then assign next call to RN who made last call
         */

        $match['window_match'] .= '('. User::find($match['nurse'])->display_name . ')';

        return $match;
    }

    public function getMostFrequentNursesForPatient()
    {

        //get all nurses that can care for a patient
        $nurses = Nurse::whereHas('user', function ($q) {

            $q->where('user_status', 1);
        })->get();

        //Result array with Nurses
        $canCare = [];

        foreach ($nurses as $nurse) {
            //get all locations for nurse
            $nurse_programs = $nurse->user->viewableProgramIds();

            $intersection = in_array($this->patient->user->program_id, $nurse_programs);

            if ($intersection) {
                $successfulCallCount =
                    Call
                        ::where('outbound_cpm_id', $nurse->user_id)
                        ->where('inbound_cpm_id', $this->patient->user_id)
                        ->where('status', 'reached')
                        ->count();

                $canCare[$nurse->user_id] = $successfulCallCount;
            }
        }

        arsort($canCare);

        return $canCare;
    }


    public function checkNurseForTargetDays(
        $nurse
    ) {

        //see if there are any window intersections within the next 3 days.
        //supplies $this->matchArray
        $date_matches = $this->checkForIntersectingDays($nurse); //first days

        if (!is_null($date_matches)) {
            foreach ($date_matches as $key => $value) {
                if (isset($value['patient']) && isset($value['nurse'])) {
                    if ($this->checkForIntersectingTimes($value['patient'], $value['nurse'])) {
                        $startWindow = Carbon::parse($value['patient']['window_start']);
                        $endWindow = Carbon::parse($value['patient']['window_end']);

                        $match['date'] = $startWindow->toDateString();
                        $match['window_start'] = $startWindow->format('H:i');
                        $match['window_end'] = $endWindow->format('H:i');

                        $match['window_match'] = 'We found an intersecting nurse window with: ' . $nurse->user->fullName;
                        $match['nurse'] = $nurse->user_id;

                        return $match;
                    }
                }
            }
        }

        //nurse has no windows
        return false;
    }



//finds any days that have windows for patient and nurse
//supplies $this->matchArray()

    public function checkForIntersectingDays(
        $nurse
    ) {

        $matchArray = [];

        $patientWindow['date'] = Carbon::parse($this->offsetDate)->toDateString();
        $patientWindow['window_start'] = $this->windowStart;
        $patientWindow['window_end'] = $this->windowEnd;

        $targetDays = [

            Carbon::parse($patientWindow['date']),
            Carbon::parse($patientWindow['date'])->addDays(1),
            Carbon::parse($patientWindow['date'])->addDays(2),

        ];

        $patientUpcomingWindows = $this->getNextWindowsForPatient();

        foreach ($targetDays as $day) {
            $dayString = $day->toDateString();

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//

            //CHECK for nurse window on target day

            $nurseWindow = $nurse->windows->first(function (
                $value,
                $key
            ) use
                (
                $dayString
            ) {
                //@todo CHANGE THIS PART
                //check whether any days fall in this window
                return $value->date->toDateString() == $dayString;
            });

            if ($nurseWindow != null) {
                $matchArray[$dayString]['nurse'] = clhWindowToTimestamps(
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
            ) use
                (
                $day
            ) {

                return Carbon::parse($value['window_start'])->toDateString() == $day->toDateString();
            })->first();

            if ($patientWindow != null) {
                $matchArray[$dayString]['patient'] = $patientWindow;
            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
        }

        return $matchArray;
    }

    public function getNextWindowsForPatient()
    {

        $patient_windows = $this->patient->patientContactWindows->all();

        //to count the current day in the calculation as well, we sub one day.
        $offset_date = Carbon::parse($this->offsetDate)->subDay()->toDateString();

        //If there are no contact windows, we just return the same day. @todo confirm logic
        if (!$patient_windows) {
            $carbon_date_start = Carbon::parse($offset_date);
            $carbon_date_end = Carbon::parse($offset_date);

            $carbon_date_start->setTime('10', '00');
            $carbon_date_end->setTime('12', '00');

            $windows[0]['window_start'] = $carbon_date_start->addDay()->toDateTimeString();
            $windows[0]['window_end'] = $carbon_date_end->addDay()->toDateTimeString();

            return collect($windows);
        }

        $windows = [];

        // leaving first blank to offset weird way of storing week as 1-7 instead of 0-6.
        // Returns a datetime string with all the necessary time information
        $week = [
            '',
            Carbon::MONDAY,
            Carbon::TUESDAY,
            Carbon::WEDNESDAY,
            Carbon::THURSDAY,
            Carbon::FRIDAY,
            Carbon::SATURDAY,
            Carbon::SUNDAY,
        ];
        $count = 0;

        //The date from the algorithm is supplied here to find the amount of time we wait before calling him back.

        //In this section, we loop through the next days of the week that the patient is available,
        //and after assigning the times to the carbon object, we add it to an array as available
        //contact days

        //Since we need a static date to keep adding to
        //$offset_date = $offset_date->toDateTimeString();

        $weeks_range = 2;

        for ($i = 0; $i < $weeks_range; $i++) {
            //add windows for each week needed.

            foreach ($patient_windows as $window) {
                //the first date should include the target date, so we backtrack one day
                //and see whether the date is a window.

                if ($i == 0) {
                    $carbon_date_start = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                    $carbon_date_end = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                } else {
                    $carbon_date_start = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                    $carbon_date_end = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                }

                $carbon_hour_start = Carbon::parse($window->window_time_start)->format('H');
                $carbon_minutes_start = Carbon::parse($window->window_time_start)->format('i');

                $carbon_hour_end = Carbon::parse($window->window_time_end)->format('H');
                $carbon_minutes_end = Carbon::parse($window->window_time_end)->format('i');

                $carbon_date_start->setTime($carbon_hour_start, $carbon_minutes_start);
                $carbon_date_end->setTime($carbon_hour_end, $carbon_minutes_end);

                $windows[$count]['window_start'] = $carbon_date_start->toDateTimeString();
                $windows[$count]['window_end'] = $carbon_date_end->toDateTimeString();
                $count++;
            }
        }

        return collect($windows)->sort();
    }

//for every day window-pair given for nurses and patients, this will return whether they intersect.
//supplies $this->matchArray()

    public function checkForIntersectingTimes(
        $patientWindow,
        $nurseWindow
    ) {

        $patientStartCarbon = Carbon::parse($patientWindow['window_start']);
        $patientEndCarbon = Carbon::parse($patientWindow['window_end']);

        $nurseStartCarbon = Carbon::parse($nurseWindow['window_start'])->subMinutes(15); //padding
        $nurseEndCarbon = Carbon::parse($nurseWindow['window_end'])->addMinutes(15); //padding

        //any overlap is true
        return ($patientStartCarbon < $nurseEndCarbon) && ($patientEndCarbon > $nurseStartCarbon);
    }

    public function countScheduledCallCountForNurseForWindow(
        $date_matches
    ) {

        return Call::where('outbound_cpm_id', $date_matches['nurse'])
            ->where('scheduled_date', $date_matches['date'])
            ->where('window_start', '>=', $date_matches['window_start'])
            ->where('window_end', '<=', $date_matches['window_end'])
            ->count();
    }

    public function getLastRNCallWithoutAttemptNote($patient, $nurseToIgnore)
    {

        $call = Call
            ::where('inbound_cpm_id', $patient->user_id)
            ->where('status', '!=', 'scheduled')
            ->where('called_date', '!=', '')
            ->where('outbound_cpm_id', '!=', $nurseToIgnore)
            ->where('attempt_note', '=', '')
            ->orderBy('called_date', 'desc')
            ->first()['outbound_cpm_id'];

        return $call;
    }
}
