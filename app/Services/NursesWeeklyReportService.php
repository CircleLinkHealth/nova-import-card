<?php

namespace App\Services;

use App\SaasAccount;
use App\User;
use Carbon\Carbon;

class NursesWeeklyReportService
{
    public function showDataFromDb(Carbon $date)
    {
        $data = $this->collectData($date);

        return $data;
    }

    public function collectData(Carbon $date)
    {
        $oneWeekBeforeYesterday = Carbon::today()->startOfDay();
        $data                   = [];
        $nurses                 = User::ofType('care-center')
                                      ->with([
                                          'nurseInfo.windows',
                                          'pageTimersAsProvider' => function ($q) use ($date) {
                                              $q->where([
                                                  ['start_time', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                                                  ['end_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                                              ]);
                                          },
                                          'outboundCalls'        => function ($q) use ($date) {
                                              $q->where('scheduled_date', $date->toDateString())
                                                ->orWhere('called_date', '>=', $date->toDateTimeString())
                                                ->where('called_date', '<=',
                                                    $date->copy()->endOfDay()->toDateTimeString());
                                          },
                                      ])->whereHas('outboundCalls', function ($q) use ($date) {
                $q->where('scheduled_date', $date->toDateString())
                  ->orWhere('called_date', '>=', $date->toDateTimeString());
            })->chunk(10, function ($nurses) use (&$data, $date) {
                foreach ($nurses as $nurse) {
                    $data[] = collect([
                        //changed to user id
                        'nurse_id'  => $nurse->id,
//                        'name'           => $nurse->first_name,
//                        'last_name'      => $nurse->last_name,
                        'actualHours'    => $nurse->pageTimersAsProvider->sum('billable_duration'),
                        'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                            return $window->numberOfHoursCommitted();
                        }),
                        'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                        'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->count(),
                        'successful'     => $nurse->outboundCalls->where('status', 'reached')->count(),
                        'unsuccessful'   => $nurse->outboundCalls->whereIn('status',
                            ['not reached', 'dropped'])->count(),
                    ]);
                }
            });

        return collect($data);
    }

    //UploadDataS3 runs daily @ 23:30 using Scheduled Command

    public function showDataFromS3(Carbon $date)
    {
        $noReportDates = Carbon::parse('2019-1-06');
        $json          = optional(SaasAccount::whereSlug('circlelink-health')
                                             ->first()
                                             ->getMedia("nurses-weekly-report-{$date->toDateString()}.json")
                                             ->sortByDesc('id')
                                             ->first())
            ->getFile();
        //first check if we have a valid file
        if ( ! $json || $date <= $noReportDates) {
            throw new \Exception('File doesnt exists for selected date.', 500);
        } else {
            //then check if it's in json format
            if ( ! is_json($json)) {
                throw new \Exception('File retrieved is not in json format.', 500);
            }
            $data = json_decode($json, true);
        }

        return $data;
    }

    /* public function uploadDataToS3(Carbon $date)
     {
         $date   = Carbon::now();
         $data   = [];
         $nurses = User::ofType('care-center')
                       ->with([
                           'nurseInfo.windows',
                           'pageTimersAsProvider' => function ($q) use ($date) {
                               $q->where([
                                   ['start_time', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                                   ['end_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                               ]);
                           },
                           'outboundCalls'        => function ($q) use ($date) {
                               $q->where('scheduled_date', $date->toDateString())
                                 ->orWhere('called_date', '>=', $date->toDateTimeString())
                                 ->where('called_date', '<=', $date->copy()->endOfDay()->toDateTimeString());
                           },
                       ])->whereHas('outboundCalls', function ($q) use ($date) {
                 $q->where('scheduled_date', $date->toDateString())
                   ->orWhere('called_date', '>=', $date->toDateTimeString());
             })->chunk(10, function ($nurses) use (&$data, $date) {
                 foreach ($nurses as $nurse) {
                     $data[] = [
                         'nurse_info_id'  => $nurse->nurseInfo->id,
                         'name'           => $nurse->first_name,
                         'last_name'      => $nurse->last_name,
                         'actualHours'    => $nurse->pageTimersAsProvider->sum('billable_duration'),
                         'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                             carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                             return $window->numberOfHoursCommitted();
                         }),
                         'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                         'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                             ['reached', 'not reached', 'dropped'])->count(),
                         'successful'     => $nurse->outboundCalls->where('status', 'reached')->count(),
                         'unsuccessful'   => $nurse->outboundCalls->whereIn('status',
                             ['not reached', 'dropped'])->count(),
                     ];
                 }
             });
         $path  = storage_path("nurses-weekly-report-{$date->toDateString()}.json");
         $saved = file_put_contents($path, json_encode($data));

         if ( ! $saved) {
             if (app()->environment('worker')) {
                 sendSlackMessage(
                     '#callcenter_ops',
                     "Nurses weekly calls and work hours report {$date->toDateString()} could not be created. \n"
                 );
             }
         }
         SaasAccount::whereSlug('circlelink-health')
                    ->first()
                    ->addMedia($path)
                    ->toMediaCollection("nurses-weekly-report-{$date->toDateString()}.json");

         if (app()->environment('worker')) {
             sendSlackMessage(
                 '#callcenter_ops',
                 "Nurses weekly calls and work hours report {$date->toDateString()} created. \n"
             );
         }

         return info('Daily Nurses Calls & Work hrs uploaded to S3');
     }*/

    /* public function collectDataToUpload()
     {
         $date   = Carbon::now();
         $data   = [];
         $nurses = User::ofType('care-center')
                       ->with([
                           'nurseInfo.windows',
                           'pageTimersAsProvider' => function ($q) use ($date) {
                               $q->where([
                                   ['start_time', '>=', $date->copy()->startOfDay()->toDateTimeString()],
                                   ['end_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                               ]);
                           },
                           'outboundCalls'        => function ($q) use ($date) {
                               $q->where('scheduled_date', $date->toDateString())
                                 ->orWhere('called_date', '>=', $date->toDateTimeString())
                                 ->where('called_date', '<=', $date->copy()->endOfDay()->toDateTimeString());
                           },
                       ])->whereHas('outboundCalls', function ($q) use ($date) {
                 $q->where('scheduled_date', $date->toDateString())
                   ->orWhere('called_date', '>=', $date->toDateTimeString());
             })->chunk(10, function ($nurses) use (&$data, $date) {
                 foreach ($nurses as $nurse) {
                     $data[] = [
                         'nurse_info_id'  => $nurse->nurseInfo->id,
                         'name'           => $nurse->first_name,
                         'last_name'      => $nurse->last_name,
                         'actualHours'    => $nurse->pageTimersAsProvider->sum('billable_duration'),
                         'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                             carbonToClhDayOfWeek($date->dayOfWeek))->sum(function ($window) {
                             return $window->numberOfHoursCommitted();
                         }),
                         'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->count(),
                         'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                             ['reached', 'not reached', 'dropped'])->count(),
                         'successful'     => $nurse->outboundCalls->where('status', 'reached')->count(),
                         'unsuccessful'   => $nurse->outboundCalls->whereIn('status',
                             ['not reached', 'dropped'])->count(),
                     ];
                 }
             });

         return $data;
     }*/

}


/*$oneWeekBeforeYesterday = Carbon::parse($date)->startOfWeek()->startOfDay();

        $data                   = [];
        $nurses                 = User::ofType('care-center')
                                      ->with([
                                          'nurseInfo.windows',
                                          'pageTimersAsProvider' => function ($q) use ($date, $oneWeekBeforeYesterday) {
                                              $q->where([
                                                  ['start_time', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                                                  [
                                                      'start_time',
                                                      '>=',
                                                      $oneWeekBeforeYesterday->copy()->startOfDay()->toDateTimeString(),
                                                  ],
                                              ]);
                                          },
                                          'outboundCalls'        => function ($q) use ($date, $oneWeekBeforeYesterday) {
                                              $q->where([
                                                  ['scheduled_date', '<=', $date->copy()->endOfDay()->toDateString()],
                                                  [
                                                      'scheduled_date',
                                                      '>=',
                                                      $oneWeekBeforeYesterday->copy()->startOfDay()->toDateTimeString(),
                                                  ],
                                              ])->orWhere([
                                                  ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                                                  [
                                                      'called_date',
                                                      '>=',
                                                      $oneWeekBeforeYesterday->copy()->startOfDay()->toDateTimeString(),
                                                  ],
                                              ]);
                                          },
                                      ])->whereHas('outboundCalls', function ($q) use ($date, $oneWeekBeforeYesterday) {
                $q->where([
                    ['scheduled_date', '<=', $date->copy()->endOfDay()->toDateString()],
                    ['scheduled_date', '>=', $oneWeekBeforeYesterday->copy()->startOfDay()->toDateTimeString()],
                ])->orWhere([
                    ['called_date', '<=', $date->copy()->endOfDay()->toDateTimeString()],
                    ['called_date', '>=', $oneWeekBeforeYesterday->copy()->startOfDay()->toDateTimeString()],
                ]);
            })->chunk(10, function ($nurses) use (&$data, $date, $oneWeekBeforeYesterday) {
                foreach ($nurses as $nurse) {
                    $data[] = [
                        'nurse_info_id' => $nurse->nurseInfo->id,
                        'name'          => $nurse->first_name,
                        'last_name'     => $nurse->last_name,
                        'scheduledCalls' => $nurse->outboundCalls->where('status', 'scheduled')->mapToGroups(function (
                            $q
                        ) {
                            return [$q->scheduled_date => $q->status];
                        })->map(
                            function ($q) {
                                return count($q);
                            }
                        ),
                        //if called_date === NULL its also picks it up...
                        'actualCalls'    => $nurse->outboundCalls->whereIn('status',
                            ['reached', 'not reached', 'dropped'])->mapToGroups(function ($q) {
                            return [$q->called_date => $q->status];
                        })->map(
                            function ($q) {
                                return count($q);
                            }
                        ),
                        'successfulCalls' => $nurse->outboundCalls->where('status', 'reached')
                                                                  ->mapToGroups(function ($q) {
                                                                      return [$q->called_date => $q->status];
                                                                  })->map(
                                function ($q) {
                                    return count($q);
                                }
                            ),
                        'unsuccessfulCalls' => $nurse->outboundCalls->whereIn('status', ['not reached', 'dropped'])
                                                                    ->mapToGroups(function ($q) {
                                                                        return [$q->called_date => $q->status];
                                                                    })->map(
                                function ($q) {
                                    return count($q);
                                }
                            ),
                        'actualHours'       => $nurse->pageTimersAsProvider->mapToGroups(function ($q) {
                            return [Carbon::parse($q->start_time)->copy()->toDateString() => $q->billable_duration];
                        }),              //sum('billable_duration') I HAVE TO

                        'committedHours' => $nurse->nurseInfo->windows->where('day_of_week',
                            carbonToClhDayOfWeek($date->dayOfWeek))->mapToGroups(function ($q) {
                            return [$q->day_of_week => $q->numberOfHoursCommitted()];
                        }),
                    ];
                }
            });*/