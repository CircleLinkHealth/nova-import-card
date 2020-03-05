<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Traits\NursePerformanceCalculations;
use Carbon\Carbon;
use CircleLinkHealth\Core\Exceptions\FileNotFoundException;
use CircleLinkHealth\Customer\Entities\CompanyHoliday;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\AggregatedTotalTimePerNurse;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NursesPerformanceReportService
{
    use NursePerformanceCalculations;

    const LAST_COMMITTED_DAYS_TO_GO_BACK = 10;
    const MAX_COMMITTED_DAYS_TO_GO_BACK  = 30;

    protected $aggregatedTotalTimePerNurse;

    protected $avgHoursWorkedLast10Sessions;

    protected $successfulCallsMultiplier;

    protected $timeGoal;

    protected $unsuccessfulCallsMultiplier;

    // @var CompanyHoliday[]
    private $companyHolidays;

    /**
     * @param Carbon $date This is usually yesterday's date. Assuming report runs at midnight,
     *                     and generates report for the day before
     *
     * @return Collection
     */
    public function collectData(Carbon $date)
    {
        $this->setReportSettings();

        $this->companyHolidays = CompanyHoliday::query();

        $data = [];
        User::ofType('care-center')
            ->with(
                [
                    'nurseInfo' => function ($info) {
                        $info->with(
                            [
                                'windows',
                                'holidays',
                                'workhourables',
                            ]
                        );
                    },
                ]
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active')
                        ->when(isProductionEnv(), function ($info) {
                            $info->where('is_demo', false);
                        });
                }
            )
            ->chunk(
                35,
                function ($nurses) use (&$data, $date) {
                    $aggregatedTime = new AggregatedTotalTimePerNurse(
                        $nurses->pluck('id')->all(),
                        $date->copy()->startOfDay(),
                        $date->copy()->endOfDay()
                    );

                    foreach ($nurses as $nurse) {
                        $data[] = $this->getDataForNurse($nurse, $date, $aggregatedTime->totalSystemTime($nurse->id));
                    }
                }
            );

        return collect($data);
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
     *
     * Sets up data needed by both Nurse and States Dashboard and EmailRNDailyReport
     * @return Collection
     */
    public function getDataForNurse(User $nurse, Carbon $date, int $totalSystemTime): Collection
    {
        $patientsForMonth = $this->getUniquePatientsAssignedForNurseForMonth($nurse, $date);

        $data = [
            'nurse_id'        => $nurse->id,
            'nurse_full_name' => $nurse->getFullName(),
            'systemTime'      => $totalSystemTime,
            'actualHours'     => round((float) ($totalSystemTime / 3600), 1),
            'committedHours'  => $nurse->nurseInfo->isOnHoliday($date, $this->companyHolidays)
                ? 0
                : $nurse->nurseInfo->getHoursCommittedForCarbonDate($date),
            'scheduledCalls'                 => $nurse->countScheduledCallsFor($date),
            'actualCalls'                    => $nurse->countCompletedCallsFor($date),
            'successful'                     => $nurse->countSuccessfulCallsFor($date),
            'unsuccessful'                   => $nurse->countUnsuccessfulCallsFor($date),
            'totalMonthSystemTimeSeconds'    => $this->getTotalMonthSystemTimeSeconds($nurse, $date),
            'uniquePatientsAssignedForMonth' => $patientsForMonth->count(),
        ];

        //new metrics
        $data['completionRate']   = $this->getCompletionRate($data);
        $data['efficiencyIndex']  = $this->getEfficiencyIndex($data);
        $data['caseLoadComplete'] = $this->percentageCaseLoadComplete($patientsForMonth);
//        $data['caseLoadNeededToComplete']  = $this->estHoursToCompleteCaseLoadMonth($patientsForMonth);
        $data['caseLoadNeededToComplete']  = $this->estHoursToCompleteCaseLoadMonth($nurse, $date, $patientsForMonth);
        $data['hoursCommittedRestOfMonth'] = $this->getHoursCommittedRestOfMonth(
            $nurse,
            $nurse->nurseInfo->upcomingHolidaysFrom($date),
            $date
        );

        //newer metrics cpm-2085
        $data['avgCCMTimePerPatient'] = $this->estAvgCCMTimePerMonth($date, $patientsForMonth);
        $data['avgCompletionTime']    = $this->getAvgCompletionTime($nurse, $date, $patientsForMonth);
        $data['incompletePatients']   = $this->getIncompletePatientsCount($patientsForMonth);

        //only for EmailRNDailyReport
        $nextUpcomingWindow = $nurse->nurseInfo->firstWindowAfter(Carbon::now());
        //only for EmailRNDailyReport new version
        $data['completedPatients'] = $this->getTotalCompletedPatientsOfNurse($date, $patientsForMonth);

        if ($nextUpcomingWindow) {
            $carbonDate              = Carbon::parse($nextUpcomingWindow->date);
            $nextUpcomingWindowLabel = clhDayOfWeekToDayName(
                $nextUpcomingWindow->day_of_week
            )." {$carbonDate->format('m/d/Y')}";
        }

        $workHours  = $nurse->nurseInfo->workhourables->first();
        $totalHours = $workHours && $nextUpcomingWindow
            ? (string) $workHours->{strtolower(
                clhDayOfWeekToDayName($nextUpcomingWindow->day_of_week)
            )}
            : null;

        $data['nextUpcomingWindow']           = $nextUpcomingWindow;
        $data['totalHours']                   = $totalHours;
        $data['nextUpcomingWindowLabel']      = $nextUpcomingWindowLabel ?? null;
        $data['projectedHoursLeftInMonth']    = $this->getProjectedHoursLeftInMonth($nurse, $date->copy()) ?? 0;
        $data['avgHoursWorkedLast10Sessions'] = $this->avgHoursWorkedLast10Sessions;
        $data['surplusShortfallHours']        = $this->surplusShortfallHours($data);

        return collect($data);
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
     * @param $nurse
     * @param $date
     * @param $data
     *
     * TODO: TO REMOVE
     *
     * (time_goal* - Avg. CCM Minutes per Patient assigned) X  Total patients assigned care coach / 60  [end formula]
     *
     * Time_goal =  (elapsed work days in month / total work days in month) X 30
     *
     * @return float|int
     */
    public function getHoursBehind($data, Carbon $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        $avgCCMMinutesPerPatientAssigned = ($data['totalMonthSystemTimeSeconds'] / $data['uniquePatientsAssignedForMonth']) / 60;

        $timeGoal = (calculateWeekdays($startOfMonth, $date) / calculateWeekdays(
            $startOfMonth,
            $endOfMonth
        )) * floatval($this->timeGoal);

        return round(
            (float) (($timeGoal - $avgCCMMinutesPerPatientAssigned) * $data['uniquePatientsAssignedForMonth'] / 60),
            2
        );
    }

    /**
     * @param Collection $nurseWindows
     * @param Collection $upcomingHolidays
     *
     * @return int
     */
    public function getHoursCommittedRestOfMonth(User $nurse, $upcomingHolidays, Carbon $date)
    {
        $diff = $date->diffInDays($date->copy()->endOfMonth());

        $mutableDate = $date->copy()->addDay()->startOfDay();
        $hours       = [];
        for ($i = $diff; $i > 0; --$i) {
            $isHolidayForDate = $upcomingHolidays
                ->where('date', $mutableDate)
                ->isNotEmpty();

            //we count the hours only if the nurse has not scheduled a holiday for that day.
            if ( ! $isHolidayForDate) {
                $hours[] = $nurse->nurseInfo->getHoursCommittedForCarbonDate($mutableDate);
            }

            $mutableDate->addDay()->startOfDay();
        }

        return round(array_sum($hours), 1);
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
     * There are no data on S3 before this date.
     *
     * @return Carbon
     */
    public function getLimitDate()
    {
        return Carbon::parse('2019-02-03');
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
     * Data structure:
     * Nurses < Days < Data.
     *
     * @param $days
     *
     * @throws \Exception
     *
     * @return Collection
     */
    public function manipulateData($days)
    {
        $reports = [];
        foreach ($days as $day) {
            try {
                $reports[$day->toDateString()] = $this->showDataFromS3($day);
            } catch (FileNotFoundException $exception) {
                $reports[$day->toDateString()] = [];
            }
        }

        $nurses  = [];
        $reports = collect($reports);
        foreach ($reports as $report) {
            if ( ! empty($report)) {
                $nurses[] = $report->pluck('nurse_full_name');
            }
        }

        $nurses = collect($nurses)
            ->flatten()
            ->unique()
            ->mapWithKeys(
                function ($nurse) use ($reports) {
                    $week = [];
                    foreach ($reports as $dayOfWeek => $reportPerDay) {
                        if ( ! empty($reportPerDay)) {
                            $week[$dayOfWeek] = collect($reportPerDay)->where('nurse_full_name', $nurse)->first();
                            if (empty($week[$dayOfWeek])) {
                                $week[$dayOfWeek] = [
                                    'nurse_full_name'                => $nurse,
                                    'committedHours'                 => 0,
                                    'actualHours'                    => 0,
                                    'unsuccessful'                   => 0,
                                    'successful'                     => 0,
                                    'actualCalls'                    => 0,
                                    'scheduledCalls'                 => 0,
                                    'efficiency'                     => 0,
                                    'completionRate'                 => 0,
                                    'efficiencyIndex'                => 0,
                                    'uniquePatientsAssignedForMonth' => 0,
                                    'caseLoadComplete'               => 0,
                                    'caseLoadNeededToComplete'       => 0,
                                    'hoursCommittedRestOfMonth'      => 0,
                                    'surplusShortfallHours'          => 0,
                                    'avgCCMTimePerPatient'           => 0,
                                    'avgCompletionTime'              => 0,
                                    'incompletePatients'             => 0,
                                ];
                            }
                        }
                    }

                    return [$nurse => $week];
                }
            );

        $totalsPerDay = [];
        foreach ($reports as $dayOfWeek => $reportPerDay) {
            $totalsPerDay[$dayOfWeek] = [
                'scheduledCalls'                 => $reportPerDay->sum('scheduledCalls'),
                'actualCalls'                    => $reportPerDay->sum('actualCalls'),
                'successful'                     => $reportPerDay->sum('successful'),
                'unsuccessful'                   => $reportPerDay->sum('unsuccessful'),
                'actualHours'                    => $reportPerDay->sum('actualHours'),
                'committedHours'                 => $reportPerDay->sum('committedHours'),
                'efficiency'                     => number_format($reportPerDay->avg('efficiency'), '2'),
                'completionRate'                 => number_format($reportPerDay->avg('completionRate'), '2'),
                'efficiencyIndex'                => number_format($reportPerDay->avg('efficiencyIndex'), '2'),
                'uniquePatientsAssignedForMonth' => number_format(
                    $reportPerDay->avg('uniquePatientsAssignedForMonth'),
                    '2'
                ),
                'caseLoadComplete'          => number_format($reportPerDay->avg('caseLoadComplete'), '2'),
                'caseLoadNeededToComplete'  => $reportPerDay->sum('caseLoadNeededToComplete'),
                'projectedHoursLeftInMonth' => number_format($reportPerDay->sum('projectedHoursLeftInMonth'), '2'),
                'hoursCommittedRestOfMonth' => $reportPerDay->sum('hoursCommittedRestOfMonth'),
                'surplusShortfallHours'     => $reportPerDay->sum('surplusShortfallHours'),
                'avgCCMTimePerPatient'      => $reportPerDay->sum('avgCCMTimePerPatient'),
                'avgCompletionTime'         => $reportPerDay->sum('avgCompletionTime'),
                'incompletePatients'        => $reportPerDay->sum('incompletePatients'),
            ];
        }

        $nursesDailyTotalsForView = $this->prepareTotalsForView($totalsPerDay);

        $nurses->put('totals', $nursesDailyTotalsForView);

        return $nurses;
    }

    /**
     * @param $nurse
     *
     * Replaced by new metrics: Completion Rate, Efficiency Index, Hours Behind
     *
     * @return float|int
     */
    public function nursesEfficiencyPercentageDaily(Carbon $date, $nurse)
    {
        $actualHours = PageTimer::where(
            [
                ['start_time', '>=', $date->copy()->startOfDay()],
                ['end_time', '<=', $date->copy()->endOfDay()],
                ['provider_id', $nurse->id],
            ]
        )->sum('billable_duration') / 3600;

        $activityTime = Activity::where(
            [
                ['performed_at', '>=', $date->copy()->startOfDay()],
                ['performed_at', '<=', $date->copy()->endOfDay()],
                ['provider_id', $nurse->id],
            ]
        )->sum('duration') / 3600;

        return 0 == $actualHours || 0 == $activityTime
            ? 0
            : round((float) ($activityTime / $actualHours) * 100);
    }

    /**
     *(# of patients assigned to care coach with > 20mins CCM time AND with 1 or more successful call)
     * / total # of patients assigned to Care Coach.
     *
     * @param mixed $patients
     *
     * @return false|float|int
     */
    public function percentageCaseLoadComplete($patients)
    {
        return 0 !== $patients->count()
            ? round(
                ($patients->where('patient_time', '>=', 20)
                    ->where('successful_calls', '>=', 1)
                    ->count()) / $patients->count() * 100,
                2
            )
            : 0;
    }

    /**
     * Prepares only the totals that will be used in the table.
     * 'nurse_full_name' must be named like this cause the "totals" array will be displayed in "nurse names"column.
     *
     * @param $totalsPerDay
     *
     * @return array
     */
    public function prepareTotalsForView(array $totalsPerDay)
    {
        return collect($totalsPerDay)->mapWithKeys(function ($totalsForDay, $day) {
            return [
                $day => [
                    'nurse_full_name' => 'Z - Totals for:',
                    //"Z" exists to place totals last in order.(tangy)
                    'weekDay'                        => $day,
                    'scheduledCalls'                 => $totalsForDay['scheduledCalls'],
                    'actualCalls'                    => $totalsForDay['actualCalls'],
                    'successful'                     => $totalsForDay['successful'],
                    'unsuccessful'                   => $totalsForDay['unsuccessful'],
                    'actualHours'                    => $totalsForDay['actualHours'],
                    'committedHours'                 => $totalsForDay['committedHours'],
                    'completionRate'                 => $totalsForDay['completionRate'] ?? 'N/A',
                    'efficiencyIndex'                => $totalsForDay['efficiencyIndex'] ?? 'N/A',
                    'caseLoadNeededToComplete'       => $totalsForDay['caseLoadNeededToComplete'] ?? 'N/A',
                    'projectedHoursLeftInMonth'      => $totalsForDay['projectedHoursLeftInMonth'] ?? 'N/A',
                    'hoursCommittedRestOfMonth'      => $totalsForDay['hoursCommittedRestOfMonth'] ?? 'N/A',
                    'surplusShortfallHours'          => $totalsForDay['surplusShortfallHours'] ?? 'N/A',
                    'uniquePatientsAssignedForMonth' => $totalsForDay['uniquePatientsAssignedForMonth'] ?? 'N/A',
                    'caseLoadComplete'               => $totalsForDay['caseLoadComplete'] ?? 'N/A',
                    'avgCCMTimePerPatient'           => $totalsForDay['avgCCMTimePerPatient'] ?? 'N/A',
                    'avgCompletionTime'              => $totalsForDay['avgCompletionTime'] ?? 'N/A',
                    'incompletePatients'             => $totalsForDay['incompletePatients'] ?? 'N/A',
                ],
            ];
        })->toArray();
    }

//    /**
//     *(25 - average of CCM minutes for assigned patients with under 20 minutes of CCM time)
//     * X # of assigned patients under 20 minutes / 60.
//     *
//     * OR
//     *
//     * time left for time-goal for patients under 20 minutes
//     *
//     * @param mixed $patients
//     *
//     * @return float
//     */
//    public function estHoursToCompleteCaseLoadMonth($patients)
//    {
//        return round($patients->where('patient_time', '<', 20)->sum('patient_time_left') / 60, 1);
//    }

    public function setReportSettings()
    {
        $settings = DB::table('report_settings')->get();

        $nurseSuccessful   = $settings->where('name', 'nurse_report_successful')->first();
        $nurseUnsuccessful = $settings->where('name', 'nurse_report_unsuccessful')->first();
        $timeGoal          = $settings->where('name', 'time_goal_per_billable_patient')->first();

        $this->successfulCallsMultiplier = $nurseSuccessful
            ? $nurseSuccessful->value
            : '0.25';
        $this->unsuccessfulCallsMultiplier = $nurseUnsuccessful
            ? $nurseUnsuccessful->value
            : '0.067';
        $this->timeGoal = $timeGoal
            ? $timeGoal->value
            : '30';

        return true;
    }

    /**
     * @param $day
     *
     * @throws \Exception
     * @throws \CircleLinkHealth\Core\Exceptions\FileNotFoundException
     *
     * @return mixed
     */
    public function showDataFromS3(Carbon $day)
    {
        if ($day->lte($this->getLimitDate())) {
            throw new FileNotFoundException('No reports exists before this date');
        }
        $json = optional(
            SaasAccount::whereSlug('circlelink-health')
                ->first()
                ->getMedia("nurses-and-states-daily-report-{$day->toDateString()}.json")
                ->sortByDesc('id')
                ->first()
        )
            ->getFile();

        if ( ! $json || ! is_json($json)) {
            return collect();
        }

        return collect(json_decode($json, true));
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function surplusShortfallHours($data)
    {
        return round((float) ($data['projectedHoursLeftInMonth'] - $data['caseLoadNeededToComplete']), 2);
    }
}
