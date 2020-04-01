<?php


namespace App;


use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserTotalTimeChecker
{
    const MAX_HOURS_ALLOWED_IN_DAY_KEY = 'user_total_time_checker_max_hours_in_day';
    const ALLOWED_THRESHOLD_FOR_WEEK_KEY = 'user_total_time_checker_threshold_for_week_key';

    private const MAX_HOURS_ALLOWED_IN_DAY_DEFAULT = 8;
    private const ALLOWED_THRESHOLD_FOR_WEEK_DEFAULT = 1.2;

    /**
     * @var Carbon
     */
    private $start;

    /**
     * @var Carbon
     */
    private $end;

    /**
     * @var int
     */
    private $userId;

    /** @var bool */
    private $checkAccumulatedTime;

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

        return $this->checkTime($timePerUser);
    }


    /**
     * Get time tracked per user from $start to $end,
     * grouped by date
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
            User::careCoaches()->each(function ($user) use ($result) {
                $coll = $this->getTimePerDay($user->id, $this->start, $this->end);
                $result->put($user->id, $coll);
            });
        }

        return $result;
    }

    /**
     * Get time tracked per day (seconds) of a user
     *
     * @param int $userId
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection of stdClass(es) of `date` and `duration`
     */
    private function getTimePerDay(int $userId, Carbon $start, Carbon $end)
    {
        return DB::table((new PageTimer())->getTable())
                 ->select(DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(start_time)) as date, sum(duration) as duration'))
                 ->where('provider_id', '=', $userId)
                 ->whereBetween('start_time', [$start, $end])
                 ->groupBy(DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(start_time))'))
                 ->get();
    }

    /**
     * Check time track for each user
     * Will return a collection of two entries:
     * - 'daily': For users that went over the maximum allowed time for one day
     * - 'weekly': For users that went over the maximum committed time for the last 7 days
     *
     * @param Collection $timePerUser
     *
     * @return Collection 'daily' and 'weekly'
     */
    private function checkTime(Collection $timePerUser)
    {
        $alerts = collect();

        $maxHoursForDay = self::getMaxHoursForDay();
        $thresholdForWeek = self::getThresholdForWeek();

        $timePerUser->each(function ($item, $key) use ($alerts, $maxHoursForDay, $thresholdForWeek) {
            /** @var Collection $result */
            $result = $this->checkTimeForUser($key, $item, $maxHoursForDay, $thresholdForWeek);
            $daily  = $result->get('daily', null);
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
     * and check accumulated time (sum duration in $coll) if flag is set
     *
     * @param int $userId
     * @param Collection $coll
     * @param int $maxHoursForDay
     * @param float $thresholdForWeek
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
        if ($lastEntryHours > $maxHoursForDay) {
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
        $coll->each(function ($item) use ($nurse, &$totalCommittedHours) {
            $date                = Carbon::parse($item->date);
            $totalCommittedHours += $nurse->getHoursCommittedForCarbonDate($date);
        });

        $maxHoursAllowed = $totalCommittedHours * $thresholdForWeek;

        $totalDurationOfWeekSeconds = $coll->sum(function ($item) {
            return $item->duration;
        });
        $totalDurationOfWeekHours   = $this->secondsToHours($totalDurationOfWeekSeconds);

        if ($totalDurationOfWeekHours > $maxHoursAllowed) {
            $result->put('weekly', $totalDurationOfWeekHours);
        }

        return $result;
    }

    private function secondsToHours(int $seconds)
    {
        return $seconds / 60 / 60;
    }

    public static function getMaxHoursForDay(): int
    {
        $val = AppConfig::pull(self::MAX_HOURS_ALLOWED_IN_DAY_KEY, null);
        if (null === $val) {
            return setAppConfig(self::MAX_HOURS_ALLOWED_IN_DAY_KEY, self::MAX_HOURS_ALLOWED_IN_DAY_DEFAULT);
        }

        return intval($val);
    }

    public static function getThresholdForWeek(): float
    {
        $val = AppConfig::pull(self::ALLOWED_THRESHOLD_FOR_WEEK_KEY, null);
        if (null === $val) {
            return setAppConfig(self::ALLOWED_THRESHOLD_FOR_WEEK_KEY, self::ALLOWED_THRESHOLD_FOR_WEEK_DEFAULT);
        }

        return floatval($val);
    }


}
