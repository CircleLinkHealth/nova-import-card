<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    private LocationProcessorRepository $locationRepository;
    private PatientServiceProcessorRepository $patientServiceRepository;

    public function __construct(LocationProcessorRepository $locationProcessorRepository, PatientServiceProcessorRepository $patientServiceProcessorRepository)
    {
        $this->locationRepository       = $locationProcessorRepository;
        $this->patientServiceRepository = $patientServiceProcessorRepository;
    }

    public function process(PatientMonthlyBillingDTO $patient): PatientMonthlyBillingDTO
    {
        $processorOutputCollection = collect();
        $patient->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use (&$patient, &$processorOutputCollection) {
                if ($this->shouldNotTouch($processor->code(), $patient->getLocationId(), $patient->getChargeableMonth())) {
                    return;
                }

                $output = $processor->processBilling($patient);
                if ($output->shouldSendToDatabase()) {
                    $patient->pushServiceFromOutputIfYouShould($output);
                    $processorOutputCollection->push($output);
                }
            });

        $this->patientServiceRepository->multiAttachServiceSummaries($processorOutputCollection);

        return $patient;
    }

    private function shouldNotTouch(string $csCode, int $locationId, Carbon $month): bool
    {
        return $this->locationRepository->isLockedForMonth([$locationId], $csCode, $month);
    }
}
