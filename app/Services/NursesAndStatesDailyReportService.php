<?php

namespace App\Services;

use App\Activity;
use App\Exceptions\FileNotFoundException;
use App\PageTimer;
use App\SaasAccount;
use App\User;
use Carbon\Carbon;


class NursesAndStatesDailyReportService
{
    /**
     *
     *
     * Data structure:
     * Nurses < Days < Data
     *
     *
     * @param $days
     *
     * @param $limitDate
     *
     * @return array
     */
    public function manipulateData($days, $limitDate)
    {
        $reports = [];
        foreach ($days as $day) {
            try {
                $reports[$day->toDateString()] = collect($this->showDataFromS3($day, $limitDate));
            } catch (\Exception $e) {
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
                            ];
                        }
                    }
                }

                return [$nurse => $week];
            });

        //im repeating this cause i cant find a way to get $totalsPerDay out of mapWithKeys(). any suggestions???
        $totalsPerDay = [];
        foreach ($reports as $dayOfWeek => $reportPerDay) {
            $totalsPerDay[$dayOfWeek] =
                    [
                        'scheduledCallsSum'    => $reportPerDay->sum('scheduledCalls'),
                        'actualCallsSum'       => $reportPerDay->sum('actualCalls'),
                        'successfulCallsSum'   => $reportPerDay->sum('successful'),
                        'unsuccessfulCallsSum' => $reportPerDay->sum('unsuccessful'),
                        'actualHoursSum'       => $reportPerDay->sum('actualHours'),
                        'committedHoursSum'    => $reportPerDay->sum('committedHours'),
                        'efficiency'           => $reportPerDay->sum('efficiency'),
                    ];
        }

        //Data structure -> Nurses < Days < Data
        //Totals Structure -> date < total per column
        return [
            'data'         => $nurses,
            'totalsPerDay' => $totalsPerDay,
        ];
    }

    /**
     * @param $day
     *
     * @param $limitDate
     *
     * @return mixed
     * @throws FileNotFoundException
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
            throw new \Exception ('File retrieved is not in json format.', 500);
        }
        $data = json_decode($json, true);

        return $data;
    }

    //showDataFromDb is not used
    public function showDataFromDb(Carbon $date)
    {
        $data = $this->collectData($date);

        return $data;
    }

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
                'outboundCalls'        => function ($q) use ($date) {
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
                    $data[] = collect([
                        'nurse_id'        => $nurse->id,
                        'nurse_full_name' => $nurse->getFullName(),
                        'actualHours'     => $nurse->pageTimersAsProvider->sum('billable_duration') / 3600,
                        'committedHours'  => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }),
                        'scheduledCalls'  => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'actualCalls'     => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->count(),
                        'successful'      => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessful'    => $nurse->outboundCalls->whereIn('status',
                            ['not reached', 'dropped'])->count(),
                        'efficiency'      => $this->nursesEfficiencyPercentageDaily($date, $nurse),
                    ]);
                }
            });

        return collect($data);
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

        return $actualHours == 0 || $activityTime == 0
            ? 0
            : round((float)($activityTime / $actualHours) * 100);

    }
}


