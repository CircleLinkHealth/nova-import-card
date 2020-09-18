<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use App\TimeTrackedPerDayView;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
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
     * Aggregates "billable (ccm) time" and "non billable system time" from Activities, PageTimer, and Offline Activities.
     * "billable (ccm) time" is time associated with a patient. CLH can bill insurance companies for that time (this is where our revenue comes from).
     * "system time" is the time a nurse spends in the system, which is not associated with a patient. CLH cannot bill insurances for that time.
     *
     * Returns a collection where each item has the following fields:
     *  total_time
     *  date
     *  user_id
     *  is_billable : 1 for "billable (ccm) time", and 0 for "system time"
     *
     * @return Collection
     */
    public function aggregate()
    {
        return \DB::query()
            ->fromSub(
                $this->systemTimeFromPageTimer($this->userIds)
                    ->unionAll($this->offlineSystemTime($this->userIds))
                    ->unionAll($this->totalBillableTimeMap($this->userIds))
                    ->unionAll($this->approvedDisputesTime($this->userIds)),
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
     * @return Collection
     */
    public static function get(array $userIds, Carbon $startDate, Carbon $endDate)
    {
        return (new static($userIds, $startDate, $endDate))->aggregate();
    }

    private function approvedDisputesTime(array $nurseUserIds)
    {
        $disputesTable     = (new NurseInvoiceDailyDispute())->getTable();
        $start             = $this->startDate;
        $end               = $this->endDate;
        $dateTimeField     = 'disputed_day';
        $isBillable        = false;
        $nurseInvoiceTable = (new NurseInvoice())->getTable();
        $nurseInfoTable    = (new Nurse())->getTable();

        return \DB::table($disputesTable)
            ->join($nurseInvoiceTable, "$nurseInvoiceTable.id", '=', "$disputesTable.invoice_id")
            ->join($nurseInfoTable, "$nurseInfoTable.id", '=', "$nurseInvoiceTable.nurse_info_id")
            ->select(
                \DB::raw('SUM(TIME_TO_SEC(suggested_formatted_time) - TIME_TO_SEC(disputed_formatted_time)) as total_time'),
                \DB::raw("DATE_FORMAT($dateTimeField, '%Y-%m-%d') as date"),
                "$nurseInfoTable.user_id as user_id",
                $isBillable
                    ? \DB::raw('TRUE as is_billable')
                    : \DB::raw('FALSE as is_billable')
            )
            ->where("$disputesTable.status", '=', 'approved')
            ->whereIn('user_id', $nurseUserIds)
            ->whereBetween(
                $dateTimeField,
                [
                    $start,
                    $end,
                ]
            )
            ->groupBy('date', 'user_id');
    }

    /**
     * A helper method to construct queries for tables Activities and PageTimer.
     *
     * @param mixed $isBillable
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

    /**
     * Query to get offline time as "non billable" so we can add it to "system time" sum to compensate the nurse.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function offlineSystemTime(array $nurseUserIds)
    {
        return $this->itemizedActivitiesQuery(
            $nurseUserIds,
            (new Activity())->getTable(),
            'performed_at',
            $this->startDate,
            $this->endDate,
            false
        )->where('logged_from', 'manual_input');
    }

    /**
     * Query to get system time ("non billable"). This is non-patient related time the nurse spends in the system.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function systemTimeFromPageTimer(array $nurseUserIds)
    {
        return $this->itemizedActivitiesQuery(
            $nurseUserIds,
            (new PageTimer())->getTable(),
            'start_time',
            $this->startDate,
            $this->endDate,
            false
        );
    }

    /**
     * Query to get billable (ccm) time. This is time a nurse spends working on a patient.
     *
     * @return \Illuminate\Database\Query\Builder
     */
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
