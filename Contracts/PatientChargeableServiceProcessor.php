<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;

interface PatientChargeableServiceProcessor
{
    public function fulfill(int $patientId, Carbon $monthYear);

    public function isAttached(int $patientId, Carbon $monthYear);

    // At any point in time we check if this service has been fulfilled
    public function isFulfilled(int $patientId, Carbon $monthYear);

    public function minimumNumberOfCalls(): int;

    public function minimumTimeInSeconds(): int;

    // Check if there is an entry in ChargeableMonthlySummaries where there is a fulfilled chargeable service
    public function processBilling(int $patientId, Carbon $monthYear);

    // At the beginning or end of the month. Should we attach this chargeable service to this patient and attempt to fulfill it throughout the month?
    public function shouldAttach($patientProblems, Carbon $monthYear);

    public function shouldFulfill(int $patientId, Carbon $monthYear);
    
    public function code();
    
    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary;
}
