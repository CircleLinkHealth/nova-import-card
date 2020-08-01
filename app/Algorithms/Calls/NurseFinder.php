<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

class NurseFinder
{
    protected $data;
    protected $matchArray;

    //not used anymore
    //protected $nursesForPatient;

    protected $offsetDate;

    /*
     * Locates an RN for a given patient
     * Use function handle to get (Nurse, target date, start window and end window)
     */

    /** @var Patient patient */
    protected $patient;
    protected $previousCall;
    protected $windowEnd;
    protected $windowStart;

    public function __construct(
        Patient $patient,
        Carbon $date = null,
        $windowStart = null,
        $windowEnd = null,
        Call $previousCall = null
    ) {
        $this->patient     = $patient;
        $this->offsetDate  = $date;
        $this->windowStart = $windowStart;
        $this->windowEnd   = $windowEnd;
        //$this->nursesForPatient = $this->getMostFrequentNursesForPatient();
        $this->previousCall = $previousCall;
    }

    //finds any days that have windows for patient and nurse
    //supplies $this->matchArray()

    public function checkForIntersectingDays(
        $nurse
    ) {
        $matchArray = [];

        $patientWindow['date']         = Carbon::parse($this->offsetDate)->toDateString();
        $patientWindow['window_start'] = $this->windowStart;
        $patientWindow['window_end']   = $this->windowEnd;

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
            ) use (
                $dayString
            ) {
                //@todo CHANGE THIS PART
                //check whether any days fall in this window
                return $value->date->toDateString() == $dayString;
            });

            if (null != $nurseWindow) {
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
            ) use (
                $day
            ) {
                return Carbon::parse($value['window_start'])->toDateString() == $day->toDateString();
            })->first();

            if (null != $patientWindow) {
                $matchArray[$dayString]['patient'] = $patientWindow;
            }

            //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
        }

        return $matchArray;
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

    public function checkNurseForTargetDays(
        $nurse
    ) {
        //see if there are any window intersections within the next 3 days.
        //supplies $this->matchArray
        $date_matches = $this->checkForIntersectingDays($nurse); //first days

        if ( ! is_null($date_matches)) {
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
                }
            }
        }

        //nurse has no windows
        return false;
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

        $user               = auth()->user();
        $isCurrentUserNurse = optional($user)->isCareCoach() ?? false;

        $patientNurseUsers = $this->patient->getNurses();
        if ($patientNurseUsers) {
            $patientNurseUser = $patientNurseUsers['temporary'] ?? $patientNurseUsers['permanent'];

            if ($patientNurseUser) {
                $match['nurse']              = $patientNurseUser['user']->id;
                $match['nurse_display_name'] = $patientNurseUser['user']->display_name;
                $match['window_match']       = "Assigning next call to {$patientNurseUser['user']->display_name}.";

                if (isset($patientNurseUsers['temporary'])) {
                    $match['temporary_from'] = $patientNurseUser['from'];
                    $match['temporary_to']   = $patientNurseUser['to'];

                    if (isset($patientNurseUsers['permanent'])) {
                        $alt                             = $patientNurseUsers['permanent']['user'];
                        $match['nurse_alt']              = $alt->id;
                        $match['nurse_display_name_alt'] = $alt->display_name;
                        $match['window_match_alt']       = "Assigning next call to $alt->display_name.";
                    }
                }

                return $match;
            }
        }

        if ($isCurrentUserNurse) {
            $match['nurse']        = auth()->id();
            $match['window_match'] = 'Assigning next call to current care coach.';

            return $match;
        }

        if ( ! $this->previousCall) {
            if ($isCurrentUserNurse) {
                $match['nurse']        = auth()->id();
                $match['window_match'] = 'No previous call found, assigning to you.';

                return $match;
            }

            return null;
        }

        $isPreviousCallNurseActive = false;
        $previousCallUser          = User::ofType('care-center')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->with('nurseInfo')
            ->find($this->previousCall['outbound_cpm_id']);

        if ($previousCallUser) {
            $isPreviousCallNurseActive = true;
        }

        if ( ! $isPreviousCallNurseActive) {
            if ($isCurrentUserNurse) {
                $match['nurse']        = auth()->id();
                $match['window_match'] = 'No previous call with active nurse found, assigning to you.';

                return $match;
            }
        }

        $nurseDisplayName = '';

        if ($isPreviousCallNurseActive && '' == $this->previousCall['attempt_note']) {
            $match['nurse']        = $this->previousCall['outbound_cpm_id'];
            $match['window_match'] = 'Attempt Note was empty, assigning to care person that last contacted patient. ';
            $nurseDisplayName      = $previousCallUser->display_name;
        } else {
            $data = $this->getLastRNCallWithoutAttemptNote($this->patient, $this->previousCall['outbound_cpm_id']);

            if ('' != $this->previousCall['attempt_note']) {
                $match['window_match'] = 'Attempt Note present, looking for last care person that contacted patient without one..';
            } else {
                $match['window_match'] = '';
            }

            if (null != $data) {
                $match['nurse'] = $data->id;
                $match['window_match'] .= ' Found care person that contacted patient in the past without attempt note. ';
                $nurseDisplayName = $data->display_name;
            } elseif ($isPreviousCallNurseActive) {
                //assign back to RN that first called patient
                $match['nurse'] = $this->previousCall['outbound_cpm_id'];
                $match['window_match'] .= ' No previous care person without attempt note found, assigning to last contacted care person. ';
                $nurseDisplayName = $previousCallUser->display_name;
            } else {
                return null;
            }
        }

        /*
         *
        Always schedule next call to RN who made last call EXCEPT:
        if attempt note exists for a call: then assign next call to RN who performed the last call without an attempt note
        note: if there is no such RN (ex: first call ever has an attempt note), then assign next call to RN who made last call
         */

        $match['window_match'] .= '('.$nurseDisplayName.')';
        $match['nurse_display_name'] = $nurseDisplayName;

        return $match;
    }

    /**
     * Get last RN without attempt note.
     *
     * Edit (Pangratios) - also filters out nurses that are not active
     *
     * @param \CircleLinkHealth\Customer\Entities\User $patient
     * @param int                                      $nurseToIgnore
     *
     * @return \CircleLinkHealth\Customer\Entities\User|null
     */
    public function getLastRNCallWithoutAttemptNote($patient, $nurseToIgnore)
    {
        $user = optional(Call
            ::where('inbound_cpm_id', $patient->user_id)
                ->where('status', '!=', 'scheduled')
                ->where('called_date', '!=', '')
                ->where('attempt_note', '=', '')
                ->where('outbound_cpm_id', '!=', $nurseToIgnore)
                ->whereHas('outboundUser', function ($q) {
                    $q->whereHas('nurseInfo', function ($q2) {
                        $q2->where('status', '=', 'active');
                    });
                })
                ->orderBy('called_date', 'desc')
                ->first())->outboundUser;

        return $user;
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
                $successfulCallCount = Call
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

    public function getNextWindowsForPatient()
    {
        $patient_windows = $this->patient->contactWindows->all();

        //to count the current day in the calculation as well, we sub one day.
        $offset_date = Carbon::parse($this->offsetDate)->subDay()->toDateString();

        //If there are no contact windows, we just return the same day. @todo confirm logic
        if ( ! $patient_windows) {
            $carbon_date_start = Carbon::parse($offset_date);
            $carbon_date_end   = Carbon::parse($offset_date);

            $carbon_date_start->setTime('10', '00');
            $carbon_date_end->setTime('12', '00');

            $windows[0]['window_start'] = $carbon_date_start->addDay()->toDateTimeString();
            $windows[0]['window_end']   = $carbon_date_end->addDay()->toDateTimeString();

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

        for ($i = 0; $i < $weeks_range; ++$i) {
            //add windows for each week needed.

            foreach ($patient_windows as $window) {
                //the first date should include the target date, so we backtrack one day
                //and see whether the date is a window.

                if (0 == $i) {
                    $carbon_date_start = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                    $carbon_date_end   = Carbon::parse($offset_date)->subDay()->next($week[$window->day_of_week]);
                } else {
                    $carbon_date_start = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                    $carbon_date_end   = Carbon::parse($offset_date)->addWeek($i)->next($week[$window->day_of_week]);
                }

                $carbon_hour_start    = Carbon::parse($window->window_time_start)->format('H');
                $carbon_minutes_start = Carbon::parse($window->window_time_start)->format('i');

                $carbon_hour_end    = Carbon::parse($window->window_time_end)->format('H');
                $carbon_minutes_end = Carbon::parse($window->window_time_end)->format('i');

                $carbon_date_start->setTime($carbon_hour_start, $carbon_minutes_start);
                $carbon_date_end->setTime($carbon_hour_end, $carbon_minutes_end);

                $windows[$count]['window_start'] = $carbon_date_start->toDateTimeString();
                $windows[$count]['window_end']   = $carbon_date_end->toDateTimeString();
                ++$count;
            }
        }

        return collect($windows)->sort();
    }
}
