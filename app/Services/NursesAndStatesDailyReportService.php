<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Exceptions\FileNotFoundException;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NursesAndStatesDailyReportService
{
    protected $successfulCallsMultiplier;

    protected $timeGoal;

    protected $unsuccessfulCallsMultiplier;

    public function collectData(Carbon $date)
    {
        $this->setReportSettings();

        $data = [];
        User::ofType('care-center')
            ->with(
                [
                    'nurseInfo' => function ($info) {
                        $info->with(
                            [
                                'windows',
                                'upcomingHolidays',
                            ]
                        );
                    },
                    'pageTimersAsProvider' => function ($q) use ($date) {
                        $q->where(
                            [
                                ['start_time', '>=', $date->copy()->startOfDay()],
                                ['end_time', '<=', $date->copy()->endOfDay()],
                            ]
                        );
                    },
                    'outboundCalls' => function ($q) use ($date) {
                        $q->whereBetween('called_date', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                            ->orWhere('scheduled_date', $date->toDateString());
                    },
                ]
            )
            ->whereHas(
                'nurseInfo',
                function ($info) {
                    $info->where('status', 'active');
                }
            )
            ->chunk(
                10,
                function ($nurses) use (&$data, $date) {
                    foreach ($nurses as $nurse) {
                        $data[] = $this->getDataForNurse($nurse, $date);
                    }
                }
            );

        return collect($data);
    }

    /**
     *(25 - average of CCM minutes for assigned patients with under 20 minutes of CCM time)
     * X # of assigned patients under 20 minutes / 60.
     *
     * OR
     *
     * time left for time-goal for patients under 20 minutes
     *
     * @param mixed $patients
     *
     * @return float
     */
    public function estHoursToCompleteCaseLoadMonth($patients)
    {
        return round($patients->where('patient_time', '<', 20)->sum('patient_time_left') / 60, 1);
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
     *
     * @return Collection
     */
    public function getDataForNurse($nurse, $date)
    {
        $systemTime       = $nurse->pageTimersAsProvider->sum('billable_duration');
        $patientsForMonth = $this->getUniquePatientsAssignedForNurseForMonth($nurse, $date);
        $nurseWindows     = $nurse->nurseInfo->windows;

        $data = [
            'nurse_id'        => $nurse->id,
            'nurse_full_name' => $nurse->getFullName(),
            'systemTime'      => $systemTime,
            'actualHours'     => round((float) ($systemTime / 3600), 2),
            'committedHours'  => round(
                (float) $nurseWindows->where(
                    'day_of_week',
                    carbonToClhDayOfWeek($date->dayOfWeek)
                )->sum(
                    function ($window) {
                        return $window->numberOfHoursCommitted();
                    }
                ),
                2
            ),
            'scheduledCalls' => $nurse->outboundCalls->count(),
            'actualCalls'    => $nurse->outboundCalls->whereIn(
                'status',
                ['reached', 'not reached', 'dropped']
            )->count(),
            'successful'   => $nurse->outboundCalls->where('status', 'reached')->count(),
            'unsuccessful' => $nurse->outboundCalls->whereIn(
                'status',
                ['not reached', 'dropped']
            )->count(),
            'totalMonthSystemTimeSeconds'    => $this->getTotalMonthSystemTimeSeconds($nurse, $date),
            'uniquePatientsAssignedForMonth' => $patientsForMonth->count(),
        ];

        //new metrics
        $data['completionRate']            = $this->getCompletionRate($data);
        $data['efficiencyIndex']           = $this->getEfficiencyIndex($data);
        $data['caseLoadComplete']          = $this->percentageCaseLoadComplete($patientsForMonth);
        $data['caseLoadNeededToComplete']  = $this->estHoursToCompleteCaseLoadMonth($patientsForMonth);
        $data['hoursCommittedRestOfMonth'] = $this->getHoursCommittedRestOfMonth(
            $nurseWindows,
            $nurse->nurseInfo->upcomingHolidays,
            $date
        );
        $data['surplusShortfallHours'] = $data['hoursCommittedRestOfMonth'] - $data['caseLoadNeededToComplete'];

        //only for EmailRNDailyReport
        $data['nextUpcomingWindow'] = optional($nurse->nurseInfo->firstWindowAfter($date->copy()))->toArray();

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
    public function getHoursBehind($data, $date)
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

    public function getHoursCommittedRestOfMonth($nurseWindows, $upcomingHolidays, Carbon $date)
    {
        $diff = $date->diffInDays($date->copy()->endOfMonth());

        $mutableDate = $date->copy()->addDay();
        $hours       = [];
        for ($i = $diff; $i > 0; --$i) {
            $holidayForDate = $upcomingHolidays->where('date', $mutableDate->toDateString());

            //we count the hours only if the nurse has not scheduled a holiday for that day.
            if ($holidayForDate->isEmpty()) {
                $hours[] = round(
                    (float) $nurseWindows->where(
                        'day_of_week',
                        carbonToClhDayOfWeek($mutableDate->dayOfWeek)
                    )->sum(
                        function ($window) {
                            return $window->numberOfHoursCommitted();
                        }
                    ),
                    1
                );
            }

            $mutableDate->addDay();
        }

        return array_sum($hours);
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

    public function getTotalMonthSystemTimeSeconds($nurse, $date)
    {
        return PageTimer::where('provider_id', $nurse->id)
            ->createdInMonth($date, 'start_time')
            ->sum('billable_duration');
    }

    public function getUniquePatientsAssignedForNurseForMonth($nurse, $date)
    {
        return \DB::table('calls')
            ->select(
                \DB::raw('DISTINCT inbound_cpm_id as patient_id'),
                \DB::raw(
                    'GREATEST(patient_monthly_summaries.ccm_time, patient_monthly_summaries.bhi_time)/60 as patient_time'
                      ),
                \DB::raw(
                    "({$this->timeGoal} - (GREATEST(patient_monthly_summaries.ccm_time, patient_monthly_summaries.bhi_time)/60)) as patient_time_left"
                      ),
                'no_of_successful_calls as successful_calls'
                  )
            ->leftJoin('users', 'users.id', '=', 'calls.inbound_cpm_id')
            ->leftJoin('patient_monthly_summaries', 'users.id', '=', 'patient_monthly_summaries.patient_id')
            ->whereRaw(
                "(
(
DATE(calls.scheduled_date) >= DATE('{$date->copy()->startOfMonth()->toDateString()}')
AND
DATE(calls.scheduled_date)<=DATE('{$date->toDateString()}')
) 
OR (
DATE(calls.called_date) >= DATE('{$date->copy()->startOfMonth()->toDateString()}') 
AND
DATE(calls.called_date)<=DATE('{$date->toDateString()}')
)
)
AND (calls.type IS NULL OR calls.type='call') 
AND calls.outbound_cpm_id = {$nurse->id} AND
DATE(patient_monthly_summaries.month_year) = DATE('{$date->copy()->startOfMonth()->toDateString()}')"
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
                                    'nurse_full_name'           => $nurse,
                                    'committedHours'            => 0,
                                    'actualHours'               => 0,
                                    'unsuccessful'              => 0,
                                    'successful'                => 0,
                                    'actualCalls'               => 0,
                                    'scheduledCalls'            => 0,
                                    'efficiency'                => 0,
                                    'completionRate'            => 0,
                                    'efficiencyIndex'           => 0,
                                    'caseLoadComplete'          => 0,
                                    'caseLoadNeededToComplete'  => 0,
                                    'hoursCommittedRestOfMonth' => 0,
                                    'surplusShortfallHours'     => 0,
                                ];
                            }
                        }
                    }

                    return [$nurse => $week];
                }
            );

        $totalsPerDay = [];
        foreach ($reports as $dayOfWeek => $reportPerDay) {
            $totalsPerDay[$dayOfWeek] = collect(
                [
                    'scheduledCallsSum'         => $reportPerDay->sum('scheduledCalls'),
                    'actualCallsSum'            => $reportPerDay->sum('actualCalls'),
                    'successfulCallsSum'        => $reportPerDay->sum('successful'),
                    'unsuccessfulCallsSum'      => $reportPerDay->sum('unsuccessful'),
                    'actualHoursSum'            => $reportPerDay->sum('actualHours'),
                    'committedHoursSum'         => $reportPerDay->sum('committedHours'),
                    'efficiency'                => number_format($reportPerDay->avg('efficiency'), '2'),
                    'completionRate'            => number_format($reportPerDay->avg('completionRate'), '2'),
                    'efficiencyIndex'           => number_format($reportPerDay->avg('efficiencyIndex'), '2'),
                    'caseLoadComplete'          => number_format($reportPerDay->avg('caseLoadComplete'), '2'),
                    'caseLoadNeededToComplete'  => $reportPerDay->sum('caseLoadNeededToComplete'),
                    'hoursCommittedRestOfMonth' => $reportPerDay->sum('hoursCommittedRestOfMonth'),
                    'surplusShortfallHours'     => $reportPerDay->sum('surplusShortfallHours'),
                ]
            );
        }
        $nurses->put('totals', $totalsPerDay);

        return $nurses;
    }

    /**
     * @param Carbon $date
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
     */
    public function percentageCaseLoadComplete($patients)
    {
        return 0 !== $patients->count()
            ? round(
                ($patients->where('patient_time', '>=', 20)->where(
                    'successful_calls',
                    '>=',
                    1
                    )->count() / $patients->count()) * 100,
                2
            )
            : 0;
    }

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
     * @throws FileNotFoundException
     * @throws \Exception
     *
     * @return mixed
     */
    public function showDataFromS3($day)
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

        if ( ! $json) {
            throw new \Exception('File does not exist for selected date.', 400);
        }
        if ( ! is_json($json)) {
            throw new \Exception('File retrieved is not in json format.', 500);
        }

        return collect(json_decode($json, true));
    }
}
