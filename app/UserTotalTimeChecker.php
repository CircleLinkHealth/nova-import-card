<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserTotalTimeChecker
{
    const ALLOWED_THRESHOLD_FOR_WEEK_KEY             = 'user_total_time_checker_threshold_for_week_key';
    const MAX_HOURS_ALLOWED_IN_DAY_KEY               = 'user_total_time_checker_max_hours_in_day';
    private const ALLOWED_THRESHOLD_FOR_WEEK_DEFAULT = 1.2;

    private const MAX_HOURS_ALLOWED_IN_DAY_DEFAULT = 8;

    /** @var bool */
    private $checkAccumulatedTime;

    /**
     * @var Carbon
     */
    private $end;

    /**
     * @var Carbon
     */
    private $start;

    /**
     * @var int
     */
    private $userId;

    public function __construct(Carbon $start, Carbon $end, bool $checkAccumulatedTime, $userId = null)
    {
        $this->start                = $start;
        $this->end                  = $end;
        $this->checkAccumulatedTime = $checkAccumulatedTime;
        $this->userId               = $userId;
    }

    public function check()
    {
        $timePerUser = $this->getTimePerUser();
        $filtered    = $timePerUser->filter(function (Collection $item) {
            return $item->isNotEmpty();
        });

        return $this->checkTime($filtered);
    }

    public static function getMaxHoursForDay(): int
    {
        $val = AppConfig::pull(self::MAX_HOURS_ALLOWED_IN_DAY_KEY, null);
        if (null === $val) {
            return AppConfig::set(self::MAX_HOURS_ALLOWED_IN_DAY_KEY, self::MAX_HOURS_ALLOWED_IN_DAY_DEFAULT);
        }

        return floatval($val);
    }

    public static function getThresholdForWeek(): float
    {
        $val = AppConfig::pull(self::ALLOWED_THRESHOLD_FOR_WEEK_KEY, null);
        if (null === $val) {
            return AppConfig::set(self::ALLOWED_THRESHOLD_FOR_WEEK_KEY, self::ALLOWED_THRESHOLD_FOR_WEEK_DEFAULT);
        }

        return floatval($val);
    }

    /**
     * Check time track for each user
     * Will return a collection of two entries:
     * - 'daily': For users that went over the maximum allowed time for one day
     * - 'weekly': For users that went over the maximum committed time for the last 7 days.
     *
     * @return Collection 'daily' and 'weekly'
     */
    private function checkTime(Collection $timePerUser)
    {
        $alerts = collect();

        $maxHoursForDay   = self::getMaxHoursForDay();
        $thresholdForWeek = self::getThresholdForWeek();

        $timePerUser->each(function ($item, $key) use ($alerts, $maxHoursForDay, $thresholdForWeek) {
            /** @var Collection $result */
            $result = $this->checkTimeForUser($key, $item, $maxHoursForDay, $thresholdForWeek);
            $daily = $result->get('daily', null);
            if ($daily) {
                $current = $alerts->get('daily');
                if ( ! $current) {
                    $current = collect([$key => $daily]);
                } else {
                    $current->put($key, $daily);
                }
                $alerts->put('daily', $current);
            }
            $weekly = $result->get('weekly', null);
            if ($weekly) {
                $current = $alerts->get('weekly');
                if ( ! $current) {
                    $current = collect([$key => $weekly]);
                } else {
                    $current->put($key, $weekly);
                }
                $alerts->put('weekly', $current);
            }
        });

        return $alerts;
    }

    /**
     * Check time tracked for user in last day from $coll
     * and check accumulated time (sum duration in $coll) if flag is set.
     *
     * @return Collection 'daily' if time has exceeded max for last day,
     *                    'weekly' if time has exceeded max for last 7 days
     */
    private function checkTimeForUser(int $userId, Collection $coll, int $maxHoursForDay, float $thresholdForWeek)
    {
        $result = collect();
        if ($coll->isEmpty()) {
            return $result;
        }
        $lastEntry      = $coll->last();
        $lastEntryHours = $this->secondsToHours($lastEntry->duration);
        if ($lastEntryHours > 0 && $lastEntryHours > $maxHoursForDay) {
            $result->put('daily', $lastEntryHours);
        }

        if ( ! $this->checkAccumulatedTime) {
            return $result;
        }

        /** @var Nurse $nurse */
        $nurse = Nurse::whereUserId($userId)->first();
        if ( ! $nurse) {
            return $result;
        }

        $totalCommittedHours = 0;
        $current             = $this->start->toDateString();
        //$last is excluded, that's why we add 1 day
        $last = $this->end->copy()->addDay()->toDateString();
        while ($current !== $last) {
            $date = Carbon::parse($current);
            $totalCommittedHours += $nurse->getHoursCommittedForCarbonDate($date);

            $current = $date->addDay()->toDateString();
        }

        $maxHoursAllowed = $totalCommittedHours * $thresholdForWeek;

        $totalDurationOfWeekSeconds = $coll->sum(function ($item) {
            return $item->duration;
        });
        $totalDurationOfWeekHours = $this->secondsToHours($totalDurationOfWeekSeconds);

        if ($maxHoursAllowed > 0 && $totalDurationOfWeekHours > $maxHoursAllowed) {
            $result->put('weekly', $totalDurationOfWeekHours);
        }

        return $result;
    }

    /**
     * Get time tracked per day (seconds) of a user.
     *
     * @return Collection of stdClass(es) of `date` and `duration`
     */
    private function getTimePerDay(int $userId, Carbon $start, Carbon $end)
    {
        $result = DB::table((new PageTimer())->getTable())
            ->select(DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(start_time)) as date, sum(duration) as duration'))
            ->where('provider_id', '=', $userId)
            ->whereBetween('start_time', [$start, $end])
            ->groupBy(DB::raw('date'))
            ->get()
            ->keyBy('date');

        // also go back one day and fetch time tracked that spans over 2 days
        // for example:
        // - routine runs at 00:10 am on April 4th.
        // - an erroneous user has time tracking from 18:00 pm on April 3rd and still tracking at 00:10 am on April 4th
        // - so it won't be tracked by the query above on April 4th that has $start and $end for April 3rd
        // - in order to catch it on April 5th, $start and $end is for April 4th. We have to go one day behind and
        //   check where start_time is April 3rd and end_time is April 4th
        // NOTE: 'date' here is end_time, so even if we are checking days back, it will end up in the daily warning as well.
        $newStart = $start->copy()->subDay();
        $result2  = DB::table((new PageTimer())->getTable())
            ->select(DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(end_time)) as date, sum(duration) as duration'))
            ->where('provider_id', '=', $userId)
            ->whereBetween('start_time', [$newStart, $end])
            ->where(
                DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(start_time))'),
                '!=',
                DB::raw('MAKEDATE(YEAR(end_time),DAYOFYEAR(end_time))')
            )
            ->groupBy(DB::raw('date'))
            ->get()
            ->keyBy('date');

        $result2->each(function ($item2, $date) use ($result) {
            $current = $result->has($date)
                ? intval($result->get($date)->duration)
                : 0;
            $item2->duration = $current + intval($item2->duration);
            $result->put($date, $item2);
        });

        return $result->sortBy('date');
    }

    /**
     * Get time tracked per user from $start to $end,
     * grouped by date.
     *
     * @return Collection of date -> duration
     */
    private function getTimePerUser()
    {
        $result = collect();
        if ($this->userId) {
            $coll = $this->getTimePerDay($this->userId, $this->start, $this->end);
            $result->put($this->userId, $coll);
        } else {
            User::ofType('care-center')
                ->whereHas('nurseInfo', function ($q) {
                    $q->where('status', 'active');
                })
                ->each(function ($user) use ($result) {
                    $coll = $this->getTimePerDay($user->id, $this->start, $this->end);
                    $result->put($user->id, $coll);
                });
        }

        return $result;
    }

    private function secondsToHours(int $seconds)
    {
        return $seconds / 60 / 60;
    }
}
