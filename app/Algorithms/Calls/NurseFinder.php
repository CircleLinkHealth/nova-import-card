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


    protected $data;

    public function __construct(
        Patient $patient,
        Carbon $date,
        $windowStart,
        $windowEnd
    ) {

        $this->patient = $patient;
        $this->offsetDate = $date;
        $this->windowStart = $windowStart;
        $this->windowEnd = $windowEnd;
        $this->nursesForPatient = $this->getMostFrequentNursesForPatient();

    }

    public
    function getMostFrequentNursesForPatient()
    {

        return Call
            ::select(\DB::raw('count(*) as count, outbound_cpm_id'))
            ->whereHas('outboundUser', function ($q) {
                $q->whereHas('roles', function ($k) {
                    $k->where('name', '=', 'care-center');
                });
            })
            ->where('inbound_cpm_id', $this->patient->user_id)
            ->whereStatus('reached')
            ->groupBy('outbound_cpm_id')
            ->orderBy(\DB::raw('count'), 'desc')
            ->pluck('outbound_cpm_id', 'count');

    }

    public function find()
    {

        foreach ($this->nursesForPatient as $nurseId) {

            $nurse = Nurse::where('user_id', $nurseId)->first();

            $date_match = $this->checkNurseForTargetDays($nurse); //first days

            if ($date_match) {

                $no_of_calls_in_window = $this->countScheduledCallCountForNurseForWindow($date_match);


                //check threshold for nurse
                if ($no_of_calls_in_window < 2) {


                    return $date_match;

                }// else keep looking!

            }

        }

        //No matches, return first available window of

        $date_match['nurse'] = $this->nursesForPatient->first();
        $date_match['window_match'] = 'No windows found, assigning same nurse to original patient target';

        return $date_match;

    }

    public
    function checkNurseForTargetDays(
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

    public
    function countScheduledCallCountForNurseForWindow(
        $date_matches
    ) {

        return Call::where('outbound_cpm_id', $date_matches['nurse'])
            ->where('scheduled_date', $date_matches['date'])
            ->where('window_start', '>=', $date_matches['window_start'])
            ->where('window_end', '<=', $date_matches['window_end'])
            ->count();


    }

    public
    function checkForIntersectingDays(
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
                //check whether any days fall in this window
                return $value->date->toDateString() == $dayString;

            });

            if ($nurseWindow != null) {

                $matchArray[$dayString]['nurse'] = clhWindowToTimestamps($nurseWindow['date'],
                    $nurseWindow['window_time_start'],
                    $nurseWindow['window_time_end']);
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

//for every day window-pair given for nurses and patients, this will return whether they intersect.
//supplies $this->matchArray()

    public
    function getNextWindowsForPatient()
    {

        $patient_windows = $this->patient->patientContactWindows->all();

        //to count the current day in the calculation as well, we sub one day.
        $offset_date = Carbon::parse($this->offsetDate)->subDay()->toDateString();

        $windows = [];

        //If there are no contact windows, we just the same day. @todo confirm logic
        if (!$patient_windows) {

            $carbon_date_start = Carbon::parse($offset_date);
            $carbon_date_end = Carbon::parse($offset_date);

            $carbon_date_start->setTime('10', '00');
            $carbon_date_end->setTime('12', '00');

            $windows[0]['window_start'] = $carbon_date_start->toDateTimeString();
            $windows[0]['window_end'] = $carbon_date_end->toDateTimeString();

            return collect($windows);

        }

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

    public
    function checkForIntersectingTimes(
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


}