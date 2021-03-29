<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BillablePatientsForMonthDTO
{
    public bool $isClosed;
    public LengthAwarePaginator $summaries;

    public function __construct(LengthAwarePaginator $summaries, bool $isClosed)
    {
        $this->summaries = $summaries;
        $this->isClosed  = $isClosed;
    }

    public function toArray(): array
    {
        return [
            'summaries' => $this->summaries,
            'is_closed' => $this->isClosed,
        ];
    }
}
