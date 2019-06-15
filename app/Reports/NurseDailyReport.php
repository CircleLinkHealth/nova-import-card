<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Reports;

use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\TotalTimeAggregator;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;

class NurseDailyReport
{
    public static function data(Carbon $forDate = null)
    {
        $date = $forDate ?? Carbon::now();

        $nurse_users = User::careCoaches()
            ->where('access_disabled', 0)
            ->get();

        $nurseSystemTimeMap = TotalTimeAggregator::get(
            $nurse_users->pluck('id')->all(),
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay()
        );

        $nurses = [];

        $i = 0;

        foreach ($nurse_users as $nurse) {
            $mostRecentPageTimer = PageTimer::select('end_time')
                ->where('provider_id', $nurse->id)
                ->orderBy('end_time', 'desc')
                ->first();

            if ( ! optional($mostRecentPageTimer)->end_time) {
                continue;
            }

            $nurses[$i]['name']                     = $nurse->getFullName();
            $nurses[$i]['Time Since Last Activity'] = Carbon::parse($mostRecentPageTimer->end_time)->diffForHumans();

            $nurses[$i]['# Scheduled Calls Today']  = $nurse->countScheduledCallsFor($date);
            $nurses[$i]['# Completed Calls Today']  = $nurse->countCompletedCallsFor($date);
            $nurses[$i]['# Successful Calls Today'] = $nurse->countSuccessfulCallsFor($date);

            $aggregateObject = $nurseSystemTimeMap->map(function ($item) use ($nurse) {
                return $item->first()->where('user_id', $nurse->id)->where('is_billable', 1)->first();
            })->filter()->first();

            $activity_time = $aggregateObject
                ? (int) $aggregateObject->total_time
                : 0;

            $H1                      = floor($activity_time / 3600);
            $m1                      = ($activity_time / 60) % 60;
            $s1                      = $activity_time % 60;
            $activity_time_formatted = sprintf('%02d:%02d:%02d', $H1, $m1, $s1);

            $system_time = PageTimer::where('provider_id', $nurse->id)
                ->createdOn($date, 'updated_at')
                ->sum('billable_duration');

            $system_time_formatted = secondsToHMS($system_time);

            $nurses[$i]['CCM Mins Today']   = $activity_time_formatted;
            $nurses[$i]['Total Mins Today'] = $system_time_formatted;

            $carbon_now = Carbon::now();

            $nurses[$i]['lessThan20MinsAgo'] = false;

            $carbon_last_act             = Carbon::parse($mostRecentPageTimer->end_time);
            $nurses[$i]['last_activity'] = $carbon_last_act->toDateTimeString();

            $diff = $carbon_now->diffInSeconds($carbon_last_act);

            if ($diff <= Constants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS) {
                $nurses[$i]['lessThan20MinsAgo'] = true;
            }

            ++$i;
        }

        $nurses = collect($nurses);
        $nurses->sortBy('last_activity');

        return $nurses;
    }
}
