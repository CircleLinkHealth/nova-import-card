<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;

interface ChargeablePatientUser
{
    public function attach(Carbon $monthYear);

    public function fulfill(Carbon $monthYear);

    public function isAttached(Carbon $monthYear);

    // At any point in time we check if this service has been fulfilled
    public function isFulfilled(Carbon $monthYear);

    // Check if there is an entry in ChargeableMonthlySummaries where there is a fulfilled chargeable service
    public function processBilling(Carbon $monthYear);

    // At the beginning or end of the month. Should we attach this chargeable service to this patient and attempt to fulfill it throughout the month?
    public function shouldAttach(Carbon $monthYear);

    public function shouldFulfill(Carbon $monthYear);
}
