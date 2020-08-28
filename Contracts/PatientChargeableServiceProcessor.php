<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;

interface PatientChargeableServiceProcessor
{
    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary;

    public function code(): string;

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary;

    public function isAttached(int $patientId, Carbon $chargeableMonth): bool;

    public function isFulfilled(int $patientId, Carbon $chargeableMonth): bool;

    public function minimumNumberOfCalls(): int;
    
    public function minimumNumberOfProblems(): int;

    public function minimumTimeInSeconds(): int;

    public function processBilling(int $patientId, Carbon $chargeableMonth);

    public function repo(): PatientProcessorEloquentRepository;

    public function shouldAttach(Carbon $monthYear, PatientProblemForProcessing ...$patientProblems): bool;

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth): bool;
}
