<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Services\NursesPerformanceReportService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Support\Collection;

trait NursePerformanceCalculations
{
    /**
     * In dashboard is named : "Avg CCM Time Per Successful Patient". It changes daily (Similar to case completion).
     *
     * @param $patientsForMonth
     * @param $totalMonthlyCompletedPatientsOfNurse
     *
     * @return float|int
     */
    public function estAvgCCMTimePerMonth($patientsForMonth, $totalMonthlyCompletedPatientsOfNurse)
    {
        $totalCCMtimeOnCompletedPatients = $this->queryMonthlyCompletedPatient($patientsForMonth)->sum('patient_time');

        return 0 === $totalMonthlyCompletedPatientsOfNurse
            ? 0
            : round((float) ($totalCCMtimeOnCompletedPatients / $totalMonthlyCompletedPatientsOfNurse), 2);
    }

    public function estHoursToCompleteCaseLoadMonth(User $nurse, Carbon $date, $patientsForMonth, $totalMonthlyCompletedPatientsOfNurse, $successfulCalls)
    {
        $avgCompletionPerPatient = $this->getAvgCompletionTime($nurse, $date, $totalMonthlyCompletedPatientsOfNurse);
        $incompletePatientsCount = $this->getIncompletePatientsCount($patientsForMonth);

        return round(($avgCompletionPerPatient * $incompletePatientsCount) / 60, 1);
        //        This is the old calculation
//        return round($patients->where('patient_time', '<', 20)->sum('patient_time_left') / 60, 1);
    }

    /**
     * @param $monthlyHours
     *
     * @return int|mixed
     */
    public function extrapolateData(Carbon $emptyWindowDate, $monthlyHours)
    {
        $dayOfWeek             = carbonToClhDayOfWeek($emptyWindowDate->dayOfWeek);
        $extrapolatedWindowHrs = 0;
        foreach ($monthlyHours as $day) {
//                note:if day is holiday then 'hours' will be zero
            if ($day['dayOfWeek'] === $dayOfWeek && $day['hours'] > 0) {
                // find the first day (same dayOfWeek) which has data and then exit loop
                $extrapolatedWindowHrs = $day['hours'];
                break;
            }
        }

        return $extrapolatedWindowHrs;
    }

    /**
     * @param $monthlyHours
     *
     * @return mixed
     */
    public function extrapolateMissingWindows($monthlyHours, Carbon $givenDate)
    {
        return collect($monthlyHours)->transform(function ($week) use ($monthlyHours, $givenDate) {
            $results = [];
            $emptyWindowDate = Carbon::parse($week['date']);
            //        Return only the data after the given date here
            if ($week['date'] > $givenDate->toDateString() && empty($week['hours'])) {
                $results[] = [
                    'hours' => $this->extrapolateData($emptyWindowDate, $monthlyHours),
                    'date'  => $emptyWindowDate->toDateString(),
                ];
            } elseif ($week['date'] > $givenDate->toDateString()) {
                $results[] = [
                    'hours' => $week['hours'],
                    'date'  => $emptyWindowDate->toDateString(),
                ];
            }

            return $results;
        });
    }

    /**
     * @return float|int
     */
    public function getAvgCompletionTime(User $nurse, Carbon $date, int $totalMonthlyCompletedPatientsOfNurse)
    {
        $start = $date->copy()->startOfMonth()->toDateString();
        $end   = $date->copy()->endOfMonth()->toDateString();

        $totalCPMtimeForMonth = $nurse->pageTimersAsProvider()
            ->where('start_time', '>=', $start)
            ->where('start_time', '<=', $end)
            ->sum('billable_duration');

        return 0 === $totalMonthlyCompletedPatientsOfNurse ?
            0 :
            round(($totalCPMtimeForMonth / 60) / $totalMonthlyCompletedPatientsOfNurse, 2);
    }

    /**
     * @param $nurse
     * @param $date
     * @param $data
     *
     * maximum(calls_made / calls_assigned,hours_worked/hours_committed)
     *
     * (multiplied by 100 to show percentage)
     *
     * @return mixed
     */
    public function getCompletionRate($data)
    {
        $callRate = 0 != $data['scheduledCalls']
            ? round((float) (($data['actualCalls'] / $data['scheduledCalls']) * 100), 2)
            : 0;
        $hourRate = 0 != $data['committedHours']
            ? round((float) (($data['actualHours'] / $data['committedHours']) * 100), 2)
            : 0;

        return max(
            [
                $callRate,
                $hourRate,
            ]
        );
    }

    /**
     * @param $nurse
     * @param $date
     * @param $data
     *
     * 100 X ((0.25*# of successful calls in day) + (0.067*# of unsuccessful calls in day))/(actual hours worked in day)
     *
     * @return float|int
     */
    public function getEfficiencyIndex($data)
    {
        return 0 != $data['actualHours']
            ? intval(
                round(
                    (float) (100 * (
                        (floatval($this->successfulCallsMultiplier) * $data['successful']) + (floatval(
                            $this->unsuccessfulCallsMultiplier
                        ) * $data['unsuccessful'])
                    ) / $data['actualHours'])
                )
            )
            : 0;
    }

    /**
     * If an RN committed to work only the Tuesday of the 2nd week, then we assume they worked every Tuesday for the rest of the month
     * If a week does not have any windows then will copy the first data of previous week or first of the next week.
     *
     * @param Collection $upcomingHolidays
     *
     * @return int
     */
    public function getHoursCommittedRestOfMonth(User $nurse, $upcomingHolidays, Carbon $date)
    {
//        Doing this cause date is mutating later.
        $givenDate      = $date->copy();
        $startOfMonth   = $date->startOfMonth();
        $fullMonthRange = $startOfMonth->diffInDays($startOfMonth->copy()->endOfMonth());
        $mutableDate    = $date->copy()->addDay()->startOfDay();

        $hours = [];
        for ($i = $fullMonthRange; $i > 0; --$i) {
            $isHolidayForDate = $upcomingHolidays
                ->where('date', $mutableDate)
                ->isNotEmpty();

            //we count the hours only if the nurse has not scheduled a holiday for that day.
            if ( ! $isHolidayForDate) {
                $hours[] = [
                    'hours'     => $nurse->nurseInfo->getHoursCommittedForCarbonDate($mutableDate),
                    'date'      => $mutableDate->toDateString(),
                    'dayOfWeek' => carbonToClhDayOfWeek($mutableDate->dayOfWeek),
                ];
            }

            $mutableDate->addDay()->startOfDay();
        }

        //        If whole month is not entered, extrapolate based off of entered hours for the current week
        $committedHoursExtrapolated = $this->extrapolateMissingWindows($hours, $givenDate);

        // @var Collection $committedHoursForRestOfMonth
        return round($committedHoursExtrapolated->flatten(1)->sum('hours'), 1);
    }

    /**
     * @return int
     */
    public function getIncompletePatientsCount(object $caseLoad)
    {
//        $caseLoadComplete = % percentage.
        return $caseLoad
            ->filter(function ($q) {
                return $q->patient_time < 20
                    || $q->successful_calls < NursesPerformanceReportService::MIN_CALL;
            })
            ->count();

        //        $incompletePatients = round((float)($caseLoad->count() - $totalMonthlyCompletedPatientsOfNurse));
        //        $incompletePatients3 = $caseLoad->count() * (100 - $caseLoadComplete) / 100;
    }

    /**
     * Get last X committed days of a nurse
     * excluding holidays (nurse and/or public).
     *
     * @param $nurseInfo Nurse
     * @param $nurseWindows Collection
     * @param Carbon $date Usually a date in the past, so included in calculations
     * @param $numberOfDays int Number of last days
     *
     * @throws \Exception
     *
     * @return Collection of Carbon dates
     */
    public function getLastCommittedDays(
        Nurse $nurseInfo,
        Collection $nurseWindows,
        Carbon $date,
        $numberOfDays = self::LAST_COMMITTED_DAYS_TO_GO_BACK
    ) {
        if ($numberOfDays > NursesPerformanceReportService::MAX_COMMITTED_DAYS_TO_GO_BACK) {
            throw new \Exception('numberOfDays must not exceed MAX_COMMITTED_DAYS_TO_GO_BACK');
        }

        //start going back, day by day
        //and figure out if each day is in nurse contact window and is not a holiday
        $committedDays = collect();
        $mutableDate   = $date->copy();
        $loopCount     = 0;
        while ($committedDays->count() < $numberOfDays && $loopCount < NursesPerformanceReportService::MAX_COMMITTED_DAYS_TO_GO_BACK) {
            // @var NurseContactWindow
            $window = $nurseWindows
                ->where('day_of_week', carbonToClhDayOfWeek($mutableDate->dayOfWeek))
                ->first();

            if ($window && ! $nurseInfo->isOnHoliday($mutableDate, $this->companyHolidays)) {
                //pushing date as a string, because if we leave it as carbon, it gets mutated within the collection, resulting in all entries to be the same date.
                $committedDays->push($mutableDate->toDateTimeString());
            }

            ++$loopCount;
            $mutableDate->subDay();
        }

        return $committedDays;
    }

    /**
     * @return int
     */
    public function getNumberOfDaysCommittedRestOfMonth(
        Collection $nurseWindows,
        Collection $upcomingHolidays,
        Carbon $date
    ) {
        $diff = $date->diffInDays($date->copy()->endOfMonth());

        $mutableDate = $date->copy()->addDay();
        $noOfDays    = 0;
        for ($i = $diff; $i > 0; --$i) {
            $isHolidayForDate = $upcomingHolidays
                ->where('date', $mutableDate->copy()->startOfDay())
                ->isNotEmpty();

            if ( ! $isHolidayForDate) {
                $isInWindow = $nurseWindows
                    ->where('day_of_week', carbonToClhDayOfWeek($mutableDate->dayOfWeek))
                    ->isNotEmpty();

                if ($isInWindow) {
                    ++$noOfDays;
                }
            }
            $mutableDate->addDay();
        }

        return $noOfDays;
    }

    /**
     * = (average hours worked per committed day during last 10 sessions that care coach committed to) * (number of
     * workdays that RN committed to left in month).
     *
     * @return float|null
     */
    public function getProjectedHoursLeftInMonth(User $nurse, Carbon $date)
    {
        $nurseInfo    = $nurse->nurseInfo;
        $nurseWindows = $nurseInfo->windows;

        $committedDays = collect();
        try {
            $committedDays = $this->getLastCommittedDays(
                $nurseInfo,
                $nurseWindows,
                $date,
                NursesPerformanceReportService::LAST_COMMITTED_DAYS_TO_GO_BACK
            )
                ->sortBy(function ($date) {
                    return $date;
                });
        } catch (\Exception $e) {
            \Log::error("{$e->getMessage()}");
        }

        if ($committedDays->isEmpty()) {
            return null;
        }

        $first                              = $committedDays->first();
        $totalSeconds                       = $this->getTotalSecondsInSystemSince($nurse, Carbon::parse($first));
        $avgSeconds                         = $totalSeconds / $committedDays->count();
        $this->avgHoursWorkedLast10Sessions = $avgSeconds / 3600;

        $noOfDays = $this->getNumberOfDaysCommittedRestOfMonth(
            $nurseWindows,
            $nurse->nurseInfo->upcomingHolidaysFrom($date),
            $date
        );

        return round((float) ($noOfDays * $this->avgHoursWorkedLast10Sessions), 2);
    }

    /**
     * @param $nurse
     * @param $date
     *
     * @return mixed
     */
    public function getTotalMonthSystemTimeSeconds($nurse, $date)
    {
        return PageTimer::where('provider_id', $nurse->id)
            ->createdInMonth($date, 'start_time')
            ->sum('billable_duration');
    }

    /**
     * @return mixed
     */
    public function getTotalSecondsInSystemSince(User $nurse, Carbon $date)
    {
        //
        //limitation: what if the nurse entered the system in a day she did not commit to?
        //

        return $nurse->pageTimersAsProvider()->where(
            'start_time',
            '>=',
            $date->toDateTimeString()
        )->sum('billable_duration');
    }

    /**
     * @param $nurse
     * @param $date
     * We need a collection of all the ENROLLED patients that the nurse has SCHEDULED calls with,
     * (no date restriction on scheduled calls) along with their ccm + bhi time and successful calls
     *
     * @return Collection
     */
    public function getUniquePatientsAssignedForNurseForMonth($nurse, $date)
    {
        //We first create a subquery to bring the patient summary of the date's month. If we just joined the summary table on the query below,
        //we would then need to do WHERE month_year=$date->startOfMonth at the end of the main query.
        //This would exclude patients that have scheduled calls, but no summary for the date's month.
        $sub = \DB::table('patient_monthly_summaries')
            ->select('patient_id', 'ccm_time', 'bhi_time', 'no_of_successful_calls')
            ->where('month_year', $date->copy()->startOfMonth());

        return \DB::table('calls')
            ->select(
            //avoid duplicate patients so the total count of patients is accurate
                \DB::raw('DISTINCT inbound_cpm_id as patient_id'),
                //we check null entries (in case there is no summary for the date's month)
                \DB::raw(
                    'if (GREATEST(pms.ccm_time, pms.bhi_time) is null, 0, GREATEST(pms.ccm_time, pms.bhi_time)/60) as patient_time'
                ),
                \DB::raw(
                    "if (GREATEST(pms.ccm_time, pms.bhi_time) is null, {$this->timeGoal}, ({$this->timeGoal} - (GREATEST(pms.ccm_time, pms.bhi_time)/60))) as patient_time_left"
                ),
                \DB::raw(
                    'if (pms.no_of_successful_calls is null, 0, pms.no_of_successful_calls) as successful_calls'
                )
            )
            ->leftJoin('users', 'users.id', '=', 'calls.inbound_cpm_id')
            ->leftJoinSub($sub, 'pms', function ($join) {
                $join->on('calls.inbound_cpm_id', '=', 'pms.patient_id');
            })
            ->leftJoin('patient_info', 'users.id', '=', 'patient_info.user_id')
            ->whereRaw(
                "
calls.status = 'scheduled'
AND calls.outbound_cpm_id = {$nurse->id}
AND patient_info.ccm_status = 'enrolled'"
            )
            ->get();
    }

    /**
     *(# of patients assigned to care coach with > 20mins CCM time AND with 1 or more successful call)
     * / total # of patients assigned to Care Coach.
     *
     * @param mixed $patients
     * @param $totalMonthlyCompletedPatientsOfNurse
     *
     * @return false|float|int
     */
    public function percentageCaseLoadComplete($patients, $totalMonthlyCompletedPatientsOfNurse)
    {
        return 0 !== $patients->count()
            ? round(
                ($totalMonthlyCompletedPatientsOfNurse) / $patients->count() * 100,
                2
            )
            : 0;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function surplusShortfallHours($data)
    {
        return round((float) ($data['hoursCommittedRestOfMonth'] - $data['caseLoadNeededToComplete']), 2);
    }

    /**
     * Amount of completed patients for the month.
     *
     * @param $patientsForMonth
     *
     * @return int
     */
    private function getTotalCompletedPatientsOfNurse($patientsForMonth)
    {
        return $this->queryMonthlyCompletedPatient($patientsForMonth)->count();
    }

    /**
     * @param $patientsForMonth
     *
     * @return Collection
     */
    private function queryMonthlyCompletedPatient($patientsForMonth)
    {
        return collect($patientsForMonth)
            ->where('patient_time', '>=', 20)
            ->where('successful_calls', '>=', NursesPerformanceReportService::MIN_CALL);
    }
}
