<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerProcessor
{
    //For ABP
    public function closeMonth(array $customerModelIds, Carbon $month, int $actorId): void;

    //For ABP
    public function counts(array $customerModelIds, Carbon $month): BillablePatientsCountForMonthDTO;

    //For ABP
    public function fetchApprovablePatients(array $customerModelIds, Carbon $month, int $pageSize = 30): LengthAwarePaginator;

    //For ABP
    public function openMonth(array $customerModelIds, Carbon $month): void;

    //attach, and fulfill services whenever pertinent
    public function processServicesForAllPatients(array $customerModelIds, Carbon $month): void;
}
