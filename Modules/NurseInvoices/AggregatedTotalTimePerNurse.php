<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Illuminate\Support\Collection;

class AggregatedTotalTimePerNurse
{
    /**
     * AggregatedTime Dataset.
     *
     * @var Collection
     */
    protected $aggregatedTime;

    /**
     * Time Aggregator.
     *
     * @var TotalTimeAggregator
     */
    protected $aggregator;

    /**
     * @param TotalTimeAggregator $aggregator
     */
    public function __construct(TotalTimeAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * This is time the nurse spent on pages associated with a patient. CLH can bill insurance companies for that time (this is where our revenue comes from).
     *
     * @param $nurseId
     *
     * @return int
     */
    public function totalCcmTime($nurseId)
    {
        return $this->time(true, $nurseId);
    }

    /**
     * This is the time a nurse spends in the system, which is not associated with a patient. CLH cannot bill insurances for that time.
     *
     * @param $nurseId
     *
     * @return int
     */
    public function totalSystemTime($nurseId)
    {
        return $this->time(false, $nurseId);
    }

    /**
     * @return Collection
     */
    private function aggregatedTimeCollection()
    {
        if ( ! $this->aggregatedTime) {
            $this->aggregatedTime = $this->aggregator->aggregate();
        }

        return $this->aggregatedTime;
    }

    /**
     * A helper method to get time.
     *
     * @param $billable
     * @param $nurseId
     *
     * @return int
     */
    private function time($billable, $nurseId)
    {
        $nurseObject = $this->aggregatedTimeCollection()->where('is_billable', $billable)->where('user_id', $nurseId);

        return $nurseObject
            ? (int) $nurseObject->total_time
            : 0;
    }
}
