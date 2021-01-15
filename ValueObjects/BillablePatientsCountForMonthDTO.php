<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

class BillablePatientsCountForMonthDTO
{
    public int $approved;
    public int $other;
    public int $rejected;
    public int $toQA;

    /**
     * BillablePatientsCountForMonthDTO constructor.
     */
    public function __construct(int $approved, int $toQA, int $rejected, int $other)
    {
        $this->approved = $approved;
        $this->toQA     = $toQA;
        $this->rejected = $rejected;
        $this->other    = $other;
    }

    public function toArray()
    {
        return [
            'approved' => $this->approved,
            'toQA'     => $this->toQA,
            'rejected' => $this->rejected,
            'other'    => $this->other,
        ];
    }
}
