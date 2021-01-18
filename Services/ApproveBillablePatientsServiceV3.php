<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice as PracticeProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\Core\Entities\AppConfig;

class ApproveBillablePatientsServiceV3
{
    private PracticeProcessor $practiceProcessor;

    public function __construct(PracticeProcessor $practiceProcessor)
    {
        $this->practiceProcessor = $practiceProcessor;
    }

    public function closeMonth(int $actorId, int $practiceId, Carbon $month)
    {
        return $this->practiceProcessor->closeMonth($actorId, $practiceId, $month);
    }

    public function counts(): BillablePatientsCountForMonthDTO
    {
        return new BillablePatientsCountForMonthDTO(0, 0, 0, 0);
    }

    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        $pagination     = AppConfig::pull('abp-pagination-size', 20);
        $date           = $date->copy()->startOfMonth();
        $jsonCollection = $this->practiceProcessor->fetchApprovablePatients($practiceId, $date, $pagination);
        $isClosed       = (bool) $jsonCollection->collection->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );

        return new BillablePatientsForMonthDTO($jsonCollection->resource, $isClosed);
    }
}
