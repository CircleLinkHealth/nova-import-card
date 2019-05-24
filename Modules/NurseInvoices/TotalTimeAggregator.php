<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use App\TimeTrackedPerDayView;
use Carbon\Carbon;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Support\Collection;

class TotalTimeAggregator
{
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var Carbon
     */
    protected $startDate;
    /**
     * @var array
     */
    protected $userIds;

    public function __construct(array $userIds, Carbon $startDate, Carbon $endDate)
    {
        $this->userIds   = $userIds;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Aggregates system time from Activities, PageTimer, and Offline Activities.
     *
     * Returns a collection where each item has the following fields:
     *  total_time
     *  date
     *  user_id
     *  is_billable
     *
     * @return Collection
     */
    public function aggregate()
    {
        return \DB::query()
            ->fromSub(
                $this->systemTimeFromPageTimer($this->userIds)
                    ->unionAll($this->offlineSystemTime($this->userIds))
                    ->unionAll($this->totalBillableTimeMap($this->userIds)),
                'activities'
                  )
            ->select(
                \DB::raw('SUM(total_time) as total_time'),
                'date',
                'user_id',
                'is_billable'
                  )
            ->groupBy('user_id', 'date', 'is_billable')
            ->get()
            ->groupBy(['user_id', 'date'])
            ->values();
    }

    /**
     * Get the aggregate time.
     *
     * @param array  $userIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return Collection
     */
    public static function get(array $userIds, Carbon $startDate, Carbon $endDate)
    {
        return (new static($userIds, $startDate, $endDate))->aggregate();
    }

    /**
     * @param array  $nurseUserIds
     * @param string $table
     * @param string $dateTimeField
     * @param Carbon $start
     * @param Carbon $end
     * @param mixed  $isBillable
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function itemizedActivitiesQuery(
        array $nurseUserIds,
        string $table,
        string $dateTimeField,
        Carbon $start,
        Carbon $end,
        $isBillable = false
    ) {
        return \DB::table($table)
            ->select(
                \DB::raw('SUM(duration) as total_time'),
                \DB::raw("DATE_FORMAT($dateTimeField, '%Y-%m-%d') as date"),
                'provider_id as user_id',
                $isBillable
                          ? \DB::raw('TRUE as is_billable')
                          : \DB::raw('FALSE as is_billable')
                  )
            ->whereIn('provider_id', $nurseUserIds)
            ->whereBetween(
                $dateTimeField,
                [
                    $start,
                    $end,
                ]
                  )->groupBy('date', 'user_id');
    }

    private function offlineSystemTime(array $nurseUserIds)
    {
        return $this->itemizedActivitiesQuery(
            $nurseUserIds,
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate
        )->where('logged_from', 'manual_input');
    }

    private function systemTimeFromPageTimer(array $nurseUserIds)
    {
        return $this->itemizedActivitiesQuery(
            $nurseUserIds,
            (new PageTimer())->getTable(),
            'start_time',
            $this->startDate,
            $this->endDate
        );
    }

    private function totalBillableTimeMap(array $nurseUserIds)
    {
        return $this->itemizedActivitiesQuery(
            $nurseUserIds,
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate,
            true
        );
    }

    private function totalTimeMapWitMysqlView()
    {
        return TimeTrackedPerDayView::whereIn('user_id', $this->userIds)
            ->whereBetween(
                'date',
                [
                    $this->startDate->toDateString(),
                    $this->endDate->toDateString(),
                ]
                                    )
            ->groupBy('date', 'user_id', 'is_billable')
            ->get()
            ->groupBy(['user_id', 'date', 'is_billable'])
            ->values();
    }
}
