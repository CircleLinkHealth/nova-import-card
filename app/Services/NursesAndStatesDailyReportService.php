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
use Illuminate\Support\Collection;

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
                    $data[] = $this->getDataForNurse($nurse, $date);
                }
            });

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

        return max([
            $callRate,
            $hourRate,
        ]);
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
        $systemTime = $nurse->pageTimersAsProvider->sum('billable_duration');

        $data = [
            'nurse_id'        => $nurse->id,
            'nurse_full_name' => $nurse->getFullName(),
            'systemTime'      => $systemTime,
            'actualHours'     => round((float) ($systemTime / 3600), 2),
            'committedHours'  => round((float) $nurse->nurseInfo->windows->where(
                'day_of_week',
                carbonToClhDayOfWeek($date->dayOfWeek)
            )->sum(function ($window) {
                return $window->numberOfHoursCommitted();
            }), 2),
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
            'uniquePatientsAssignedForMonth' => $this->getUniquePatientsAssignedForMonth($nurse, $date),
        ];

        $data['completionRate']  = $this->getCompletionRate($data);
        $data['efficiencyIndex'] = $this->getEfficiencyIndex($data);
        $data['hoursBehind']     = $this->getHoursBehind($data, $date);

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
            ? round((float) (100 * (
                (0.25 * $data['successful']) + (0.067 * $data['unsuccessful'])
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
    public function getHoursBehind($data, $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        $avgCCMMinutesPerPatientAssigned = ($data['totalMonthSystemTimeSeconds'] / $data['uniquePatientsAssignedForMonth']) / 60;

        $timeGoal = (calculateWeekdays($startOfMonth, $date) / calculateWeekdays($startOfMonth, $endOfMonth)) * 30;

        return round((float) (($timeGoal - $avgCCMMinutesPerPatientAssigned) * $data['uniquePatientsAssignedForMonth'] / 60), 2);
    }

    /**
     * There are no data on S3 before this date.
     *
     * @return Carbon
     */
    public function getLimitDate()
    {
        return Carbon::parse('2018-02-03');
    }

    public function getTotalMonthSystemTimeSeconds($nurse, $date)
    {
        return PageTimer::where('provider_id', $nurse->id)
            ->createdInMonth($date, 'start_time')
            ->sum('billable_duration');
    }

    public function getUniquePatientsAssignedForMonth($nurse, $date)
    {
        return Call::select('inbound_cpm_id')
            ->where('outbound_cpm_id', $nurse->id)->where([
                ['called_date', '>=', $date->copy()->startOfMonth()->startOfDay()],
                ['called_date', '<=', $date->copy()->endOfDay()],
            ])
            ->orWhere([
                ['scheduled_date', '>=', $date->copy()->startOfMonth()->startOfDay()],
                ['scheduled_date', '<=', $date->copy()->endOfDay()],
            ])
            //distinct inbound_cpm_id
            ->distinct()
            ->count();
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

        return collect(json_decode($json, true));
    }
}
