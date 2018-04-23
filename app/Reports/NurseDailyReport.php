<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/03/2017
 * Time: 3:38 AM
 */

namespace App\Reports;


use App\Activity;
use App\PageTimer;
use App\User;
use Carbon\Carbon;

class NurseDailyReport
{
    public static function data()
    {
        $nurses = [];

        $i = 0;

        $nurseUsers = User::ofType('care-center')
                          ->where('access_disabled', 0)
                          ->chunk(10, function ($nurse_users) use (&$nurses, &$i) {
                              foreach ($nurse_users as $nurse) {
                                  $nurses[$i]['name'] = $nurse->fullName;

                                  $last_activity = PageTimer::orderBy('id', 'desc')
                                                            ->where('provider_id', $nurse->id)
                                                            ->firstOrNew([
                                                                'end_time' => null,
                                                            ]);

                                  if ( ! $last_activity->end_time) {
                                      $nurses[$i]['Time Since Last Activity'] = 'N/A';
                                  } else {
                                      $nurses[$i]['Time Since Last Activity'] = Carbon::parse($last_activity->end_time)->diffForHumans();
                                  }

                                  $nurses[$i]['# Scheduled Calls Today']  = $nurse->nurseInfo->countScheduledCallsForToday();
                                  $nurses[$i]['# Completed Calls Today']  = $nurse->nurseInfo->countCompletedCallsForToday();
                                  $nurses[$i]['# Successful Calls Today'] = $nurse->nurseInfo->countSuccessfulCallsMadeToday();

                                  $activity_time = Activity::where('provider_id', $nurse->id)
                                                           ->createdToday()
                                                           ->sum('duration');

                                  $H1                      = floor($activity_time / 3600);
                                  $m1                      = ($activity_time / 60) % 60;
                                  $s1                      = $activity_time % 60;
                                  $activity_time_formatted = sprintf("%02d:%02d:%02d", $H1, $m1, $s1);

                                  $system_time = PageTimer::where('provider_id', $nurse->id)
                                                          ->createdToday('updated_at')
                                                          ->sum('billable_duration');

                                  $system_time_formatted = secondsToHMS($system_time);

                                  $nurses[$i]['CCM Mins Today']   = $activity_time_formatted;
                                  $nurses[$i]['Total Mins Today'] = $system_time_formatted;

                                  $carbon_now = Carbon::now();

                                  $nurses[$i]['lessThan20MinsAgo'] = false;

                                  if ( ! $last_activity->end_time) {
                                      $nurses[$i]['last_activity'] = 'N/A';
                                  } else {
                                      $carbon_last_act             = Carbon::parse($last_activity->end_time);
                                      $nurses[$i]['last_activity'] = $carbon_last_act->toDateTimeString();

                                      $diff = $carbon_now->diffInSeconds($carbon_last_act);

                                      if ($diff <= 1200 && $nurses[$i]['Time Since Last Activity'] != 'N/A') {
                                          $nurses[$i]['lessThan20MinsAgo'] = true;
                                      }
                                  }

                                  if ($nurses[$i]['Time Since Last Activity'] == 'N/A') {
                                      unset($nurses[$i]);
                                  }

                                  $i++;
                              }
                          });

        $nurses = collect($nurses);
        $nurses->sortBy('last_activity');

        return $nurses;
    }
}