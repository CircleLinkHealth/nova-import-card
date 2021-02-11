<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientProblemForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientServiceProcessorOutputDTO;

interface PatientServiceProcessor
{
    public function attach(): void;

    public function baseCode(): string;

    public function clashesWith(): array;

    public function code(): string;

    public function featureIsEnabled(): bool;

    public function fulfill(): void;

    public function isAttached(): bool;

    public function isFulfilled(): bool;

    public function minimumNumberOfCalls(): int;

    public function minimumNumberOfProblems(): int;

    public function minimumTimeInSeconds(): int;

    public function processBilling(PatientMonthlyBillingDTO $billingStub): PatientServiceProcessorOutputDTO;

    public function repo(): PatientServiceProcessorRepository;

    public function shouldAttach(): bool;

    public function shouldFulfill(): bool;
}
