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
//todo:Please check if this makes logic for this scenario - im trying to avoid division by zero error
        /* if ($actualHours == 0 || $activityTime == 0) {
             $actualHours  = 1;
             $activityTime = 0;
         }
         $performance = round((float)($activityTime / $actualHours) * 100);*/

        return $actualHours == 0 || $activityTime == 0
            ? 0
            : round((float)($activityTime / $actualHours) * 100);


    }

    /**
     * @param $days
     *
     * @param $limitDate
     *
     * @return array
     * @throws FileNotFoundException
     */
    public function munipulateData($days, $limitDate)
    {
        $dataPerDay = [];
        foreach ($days as $day) {
            $day                 = Carbon::parse($day);
            if ($day->lte($limitDate)) {
                throw new FileNotFoundException();
            }
            try {
                $dataPerDay[$day->toDateString()] = $this->showDataFromS3($day);
            } catch (\Exception $e) {
                $dataPerDay[$day->toDateString()] = []; //todo: return something here
            }
        }


        //get all nurses for all days - will need names to add default values **
        // $nursesNames = [];
        /* foreach ($dataPerDay as $day => $dataForDay) {
             foreach ($dataForDay as $nurse) {
                 $nursesNames[] = $nurse['nurse_full_name'];
             }
         }*/

        $totalsPerDay = [];
        $nursesNames  = [];
        $data         = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            //get all nurses for all days - will need names to add default values **
            foreach ($dataForDay as $nurse) {
                $nursesNames[] = $nurse['nurse_full_name'];
            }

            if (empty($dataForDay)) {
                // If no data for that day - then go through all nurses and add some default values **
                foreach ($nursesNames as $nurseName) {
                    $data[$nurseName][$day]
                        = [
                        'nurse_full_name' => $nurseName,
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
            $totalsPerDay[$day] =
                [
                    'scheduledCallsSum'    => array_sum(array_column($dataForDay, 'scheduledCalls')),
                    'actualCallsSum'       => array_sum(array_column($dataForDay, 'actualCalls')),
                    'successfulCallsSum'   => array_sum(array_column($dataForDay, 'successful')),
                    'unsuccessfulCallsSum' => array_sum(array_column($dataForDay, 'unsuccessful')),
                    'actualHoursSum'       => array_sum(array_column($dataForDay, 'actualHours')),
                    'committedHoursSum'    => array_sum(array_column($dataForDay, 'committedHours')),
                    'efficiency'           => array_sum(array_column($dataForDay, 'efficiency')),
                ];
            //data has per day per nurse
            //need to go into per nurse per day
            foreach ($dataForDay as $nurse) {
                if ( ! isset($data[$nurse['nurse_full_name']])) {
                    $data[$nurse['nurse_full_name']] = [];
                }
                $data[$nurse['nurse_full_name']][$day] = $nurse;
            }
        }

        foreach ($data as $nurseName => $reportPerDayArr) {
            foreach ($days as $day) {
                $day = Carbon::parse($day);
                //if no data array exists for date
                if ( ! isset($reportPerDayArr[$day->toDateString()])) {
                    $data[$nurseName][$day->toDateString()] = [
                        'nurse_full_name' => $nurseName,
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

        /*$totalsPerDay = [];
        foreach ($dataPerDay as $day => $dataForDay) {
            $totalsPerDay[$day] =
                [
                    'scheduledCallsSum'    => array_sum(array_column($dataForDay, 'scheduledCalls')),
                    'actualCallsSum'       => array_sum(array_column($dataForDay, 'actualCalls')),
                    'successfulCallsSum'   => array_sum(array_column($dataForDay, 'successful')),
                    'unsuccessfulCallsSum' => array_sum(array_column($dataForDay, 'unsuccessful')),
                    'actualHoursSum'       => array_sum(array_column($dataForDay, 'actualHours')),
                    'committedHoursSum'    => array_sum(array_column($dataForDay, 'committedHours')),
                    'efficiency'           => array_sum(array_column($dataForDay, 'efficiency')),
                ];
        }*/

        return [
            'data'         => $data,
            'totalsPerDay' => $totalsPerDay,
        ];
    }

    /**
     * @param Carbon $day
     *
     * @return mixed
     * @throws \Exception
     */
    public function showDataFromS3(Carbon $day)
    {

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


}


