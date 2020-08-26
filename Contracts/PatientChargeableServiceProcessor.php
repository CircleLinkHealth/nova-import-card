<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Illuminate\Support\Collection;

interface PatientChargeableServiceProcessor
{
    public function attach(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary;

    public function code(): string;

    public function fulfill(int $patientId, Carbon $chargeableMonth): ChargeablePatientMonthlySummary;

    public function isAttached(int $patientId, Carbon $chargeableMonth): bool;

    public function isFulfilled(int $patientId, Carbon $chargeableMonth): bool;

    public function minimumNumberOfCalls(): int;

    public function minimumTimeInSeconds(): int;

    public function processBilling(int $patientId, Carbon $chargeableMonth);

    public function repo(): PatientProcessorEloquentRepository;

    public function shouldAttach(Collection $patientProblems, Carbon $monthYear): bool;

    public function shouldFulfill(int $patientId, Carbon $chargeableMonth): bool;
}
