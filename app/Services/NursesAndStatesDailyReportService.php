<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Call;
use App\Exceptions\FileNotFoundException;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;

class NursesAndStatesDailyReportService
{
    public function collectData(Carbon $date)
    {
        $data = [];
        User::ofType('care-center')
            ->with([
                'nurseInfo.windows',
                'pageTimersAsProvider' => function ($q) use ($date) {
                    $q->where([
                        ['start_time', '>=', $date->copy()->startOfDay()],
                        ['end_time', '<=', $date->copy()->endOfDay()],
                    ]);
                },
                'outboundCalls' => function ($q) use ($date) {
                    $q->where([
                        ['called_date', '>=', $date->copy()->startOfDay()],
                        ['called_date', '<=', $date->copy()->endOfDay()],
                    ])
                        ->orWhere('scheduled_date', $date->toDateString());
                },
            ])
            ->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where([
                    ['called_date', '>=', $date->copy()->startOfDay()],
                    ['called_date', '<=', $date->copy()->endOfDay()],
                ])
                    ->orWhere('scheduled_date', $date->toDateString());
            })
            ->orwhereHas('activitiesAsProvider', function ($q) use ($date) {
                $q->where('performed_at', $date->toDateTimeString());
            })
            ->chunk(10, function ($nurses) use (&$data, $date) {
                foreach ($nurses as $nurse) {
                    $nurseData = [
                        'assignedCallsCount' => $nurse->outboundCalls->count(),
                        'actualCallsCount'   => $nurse->outboundCalls->whereIn(
                            'status',
                            ['reached', 'not reached', 'dropped']
                        )->count(),
                        //                        'scheduledCallsCount'    => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'successfulCallsCount'   => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessfulCallsCount' => $nurse->outboundCalls->whereIn(
                            'status',
                            ['not reached', 'dropped']
                        )->count(),
                        'actualHours'   => round((float) ($nurse->pageTimersAsProvider->sum('billable_duration') / 3600), 2),
                        'commitedHours' => round((float) $nurse->nurseInfo->windows->where(
                            'day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek)
                        )->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }), 2),
                    ];

                    $data[] = collect([
                        'nurse_id'        => $nurse->id,
                        'nurse_full_name' => $nurse->getFullName(),
                        'actualHours'     => $nurseData['actualHours'],
                        'committedHours'  => $nurseData['commitedHours'],
                        'scheduledCalls'  => $nurseData['assignedCallsCount'],
                        'actualCalls'     => $nurseData['actualCallsCount'],
                        'successful'      => $nurseData['successfulCallsCount'],
                        'unsuccessful'    => $nurseData['unsuccessfulCallsCount'],
                        'efficiency'      => $this->nursesEfficiencyPercentageDaily($date, $nurse),
                        'completionRate'  => $this->getCompletionRate($nurseData),
                        'efficiencyIndex' => $this->getEfficiencyIndex($nurseData),
                        'hoursBehind'     => $this->getHoursBehind($nurse, $date),
                    ]);
                }
            });

        return collect($data);
    }

    /**
     * Data structure:
     * Nurses < Days < Data.
     *
     * @param $days
     * @param $limitDate
     *
     * @throws \Exception
     *
     * @return array
     */
    public function manipulateData($days, $limitDate)
    {
        $reports = [];
        foreach ($days as $day) {
            try {
                $reports[$day->toDateString()] = collect($this->showDataFromS3($day, $limitDate));
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
            ->mapWithKeys(function ($nurse) use ($reports) {
                $week = [];
                $totalsPerDay = [];
                foreach ($reports as $dayOfWeek => $reportPerDay) {
                    if ( ! empty($reportPerDay)) {
                        $week[$dayOfWeek] = collect($reportPerDay)->where('nurse_full_name', $nurse)->first();
                        if (empty($week[$dayOfWeek])) {
                            $week[$dayOfWeek] = [
                                'nurse_full_name' => $nurse,
                                'committedHours'  => 0,
                                'actualHours'     => 0,
                                'unsuccessful'    => 0,
                                'successful'      => 0,
                                'actualCalls'     => 0,
                                'scheduledCalls'  => 0,
                                'efficiency'      => 0,
                                'completionRate'  => 0,
                                'efficiencyIndex' => 0,
                                'hoursBehind'     => 0,
                            ];
                        }
                    }

                    $totalsPerDay[$dayOfWeek] = collect(
                        [
                            'scheduledCallsSum'    => $reportPerDay->sum('scheduledCalls'),
                            'actualCallsSum'       => $reportPerDay->sum('actualCalls'),
                            'successfulCallsSum'   => $reportPerDay->sum('successful'),
                            'unsuccessfulCallsSum' => $reportPerDay->sum('unsuccessful'),
                            'actualHoursSum'       => $reportPerDay->sum('actualHours'),
                            'committedHoursSum'    => $reportPerDay->sum('committedHours'),
                            'efficiency'           => number_format($reportPerDay->avg('efficiency'), '2'),
                            'completionRate'       => number_format($reportPerDay->avg('completionRate'), '2'),
                            'efficiencyIndex'      => number_format($reportPerDay->avg('efficiencyIndex'), '2'),
                            'hoursBehind'          => $reportPerDay->sum('hoursBehind'),
                        ]
                    );
                }

                return [$nurse => $week, 'totals' => $totalsPerDay];
            });

        return $nurses;
    }

    public function nursesEfficiencyPercentageDaily(Carbon $date, $nurse)
    {
        $actualHours = PageTimer::where([
            ['start_time', '>=', $date->copy()->startOfDay()],
            ['end_time', '<=', $date->copy()->endOfDay()],
            ['provider_id', $nurse->id],
        ])->sum('billable_duration') / 3600;

        $activityTime = Activity::where([
            ['performed_at', '>=', $date->copy()->startOfDay()],
            ['performed_at', '<=', $date->copy()->endOfDay()],
            ['provider_id', $nurse->id],
        ])->sum('duration') / 3600;

        return 0 == $actualHours || 0 == $activityTime
            ? 0
            : round((float) ($activityTime / $actualHours) * 100);
    }

    /**
     * @param $day
     * @param $limitDate
     *
     * @throws FileNotFoundException
     * @throws \Exception
     *
     * @return mixed
     */
    public function showDataFromS3($day, $limitDate)
    {
        if ($day->lte($limitDate)) {
            throw new FileNotFoundException('No reports exists before this date');
        }
        $json = optional(SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->getMedia("nurses-and-states-daily-report-{$day->toDateString()}.json")
            ->sortByDesc('id')
            ->first())
            ->getFile();

        if ( ! $json) {
            throw new \Exception('File does not exist for selected date.', 400);
        }
        if ( ! is_json($json)) {
            throw new \Exception('File retrieved is not in json format.', 500);
        }

        return json_decode($json, true);
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
    private function getCompletionRate($data)
    {
        $callRate = 0 != $data['assignedCallsCount']
            ? round((float) (($data['actualCallsCount'] / $data['assignedCallsCount']) * 100), 2)
            : 0;
        $hourRate = 0 != $data['commitedHours']
            ? round((float) (($data['actualHours'] / $data['commitedHours']) * 100), 2)
            : 0;

        return max([
            $callRate,
            $hourRate,
        ]);
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
    private function getEfficiencyIndex($data)
    {
        return 0 != $data['actualHours']
            ? round((float) (100 * (
                (0.25 * $data['successfulCallsCount']) + (0.067 * $data['unsuccessfulCallsCount'])
            ) / $data['actualHours']), 2)
            : 0;
    }

    /**
     * @param $nurse
     * @param $date
     * @param $data
     *
     * (time_goal* - Avg. CCM Minutes per Patient assigned) X  Total patients assigned care coach / 60  [end formula]
     *
     * Time_goal =  (elapsed work days in month / total work days in month) X 30
     *
     * @return float|int
     */
    private function getHoursBehind($nurse, $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        $uniquePatientsAssignedForMonth = Call::select('inbound_cpm_id')
            ->where('outbound_cpm_id', $nurse->id)->where([
                ['called_date', '>=', $date->copy()->startOfDay()],
                ['called_date', '<=', $date->copy()->endOfDay()],
            ])
            ->orWhere('scheduled_date', $date->toDateString())
            //distinct inbound_cpm_id
            ->distinct()
            ->count();

        $totalMonthSystemTimeSeconds = PageTimer::where('provider_id', $nurse->id)
            ->createdInMonth($date, 'start_time')
            ->sum('billable_duration');

        $avgCCMMinutesPerPatientAssigned = ($totalMonthSystemTimeSeconds / $uniquePatientsAssignedForMonth) / 60;

        $timeGoal = (calculateWeekdays($startOfMonth, $date) / calculateWeekdays($startOfMonth, $endOfMonth)) * 30;

        return round((float) (($timeGoal - $avgCCMMinutesPerPatientAssigned) * $uniquePatientsAssignedForMonth / 60), 2);
    }
}
