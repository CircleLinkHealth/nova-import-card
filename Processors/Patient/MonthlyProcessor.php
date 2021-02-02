<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    private LocationProcessorRepository $locationRepository;

    public function __construct(LocationProcessorRepository $locationProcessorRepository)
    {
        $this->locationRepository = $locationProcessorRepository;
    }

    public function process(PatientMonthlyBillingDTO $patient): PatientMonthlyBillingDTO
    {
        $patient->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use ($patient) {
                if ($this->shouldNotTouch($processor->code(), $patient->getLocationId(), $patient->getChargeableMonth())) {
                    return;
                }

                $processor->processBilling($patient);
            });

        return $patient;
    }

    private function shouldNotTouch(string $csCode, int $locationId, Carbon $month): bool
    {
        return $this->locationRepository->isLockedForMonth([$locationId], $csCode, $month);
    }
}
