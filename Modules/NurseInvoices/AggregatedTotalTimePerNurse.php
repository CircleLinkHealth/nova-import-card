<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

class AggregatedTotalTimePerNurse
{
    protected $aggregatedTime;
    protected $aggregator;

    public function __construct(TotalTimeAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function aggregate()
    {
        $this->aggregatedTime = $this->aggregator->aggregate()->flatten();

        return $this;
    }

    public static function get(TotalTimeAggregator $aggregator)
    {
        return (new static($aggregator))->aggregate();
    }

    public function getTotalBillableTimeForNurse($nurseId)
    {
        $nurseObject = $this->aggregatedTime->where('is_billable', 1)->where('user_id', $nurseId)->first();

        return $nurseObject
            ? (int) $nurseObject->total_time
            : 0;
    }
}
