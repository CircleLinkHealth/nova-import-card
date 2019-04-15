<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\TimeTrackedPerDayView;
use Carbon\Carbon;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreateNurseInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var string
     */
    protected $addNotes;
    /**
     * @var int
     */
    protected $addTime;
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var array
     */
    protected $nurseUserIds;
    /**
     * @var int
     */
    protected $requestedBy;
    /**
     * @var Carbon
     */
    protected $startDate;
    /**
     * @var bool
     */
    protected $variablePay;

    /**
     * Create a new job instance.
     *
     * @param array  $nurseUserIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int    $requestedBy
     * @param bool   $variablePay
     * @param int    $addTime
     * @param string $addNotes
     */
    public function __construct(
        array $nurseUserIds,
        Carbon $startDate,
        Carbon $endDate,
        int $requestedBy,
        bool $variablePay = false,
        int $addTime = 0,
        string $addNotes = ''
    ) {
        $this->nurseUserIds = $nurseUserIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->requestedBy  = $requestedBy;
        $this->variablePay  = $variablePay;
        $this->addTime      = $addTime;
        $this->addNotes     = $addNotes;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //time to run: 6.6966331005096
//        $start1 = microtime(true);
//        $systemTimeMap = $this->totalTimeMap();
//        $end1 = microtime(true) - $start1;

        //time to run: 0.07391095161438
        $start2              = microtime(true);
        $systemTimeMapNoView = $this->totalTimeMapNoView();
        $end2                = microtime(true) - $start2;
    }

    /**
     * @param string $table
     * @param string $dateTimeField
     * @param Carbon $start
     * @param Carbon $end
     * @param mixed  $isBillable
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function itemizedActivitiesQuery(
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
                      'provider_id',
                      $isBillable
                          ? \DB::raw('TRUE as is_billable')
                          : \DB::raw('FALSE as is_billable')
                  )
            ->whereIn('provider_id', $this->nurseUserIds)
            ->whereBetween(
                      $dateTimeField,
                      [
                          $start,
                          $end,
                      ]
                  )->groupBy('date', 'provider_id');
    }

    private function offlineSystemTime()
    {
        return $this->itemizedActivitiesQuery(
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate
        )->where('logged_from', 'manual_input');
    }

    private function systemTimeFromPageTimer()
    {
        return $this->itemizedActivitiesQuery(
            (new PageTimer())->getTable(),
            'start_time',
            $this->startDate,
            $this->endDate
        );
    }

    private function totalBillableTimeMap()
    {
        return $this->itemizedActivitiesQuery(
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate,
            true
        );
    }

    private function totalTimeMap()
    {
        return TimeTrackedPerDayView::whereIn('user_id', $this->nurseUserIds)
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

    /**
     * @return Collection
     */
    private function totalTimeMapNoView()
    {
        return \DB::query()
            ->fromSub(
                      $this->systemTimeFromPageTimer()
                          ->unionAll($this->offlineSystemTime())
                          ->unionAll($this->totalBillableTimeMap()),
                      'activities'
                  )
            ->select(
                      \DB::raw('SUM(total_time) as total_time'),
                      'date',
                      'provider_id',
                      'is_billable'
                  )
            ->groupBy('date', 'provider_id', 'is_billable')
            ->get()
            ->groupBy(['provider_id', 'date'], false)
            ->values();
    }
}
