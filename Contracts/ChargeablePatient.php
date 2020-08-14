<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;

interface ChargeablePatient
{
    // Check if there is an entry in ChargeableMonthlySummaries where there is a fulfilled chargeable service
    public function isBillable(BillingProcesor $billMe, Carbon $monthYear);
    
    // At the beginning or end of the month. Should we attach this chargeable service to this patient and attempt to fulfill it throughout the month?
    public function shouldAttach(BillingProcesor $billMe, Carbon $monthYear);
    public function isAttached(BillingProcesor $billMe, Carbon $monthYear);
    public function attach(BillingProcesor $billMe, Carbon $monthYear);
    
    // At any point in time we check if this service has been fulfilled
    public function isFulfilled(BillingProcesor $billMe, Carbon $monthYear);
    public function shouldFulfill(BillingProcesor $billMe, Carbon $monthYear);
    public function fulfill(BillingProcesor $billMe, Carbon $monthYear);
}
