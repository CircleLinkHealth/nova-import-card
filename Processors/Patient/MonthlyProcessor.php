<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\LocationChargeableServicesForProcessing;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    private PatientServiceProcessorRepository $patientServiceRepository;

    public function __construct(PatientServiceProcessorRepository $patientServiceProcessorRepository)
    {
        $this->patientServiceRepository = $patientServiceProcessorRepository;
    }

    public function process(PatientMonthlyBillingDTO $patient): PatientMonthlyBillingDTO
    {
        $processorOutputCollection = collect();
        $patient->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use (&$patient, &$processorOutputCollection) {
                if ($this->shouldNotTouch($patient, $processor->code())) {
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

    private function shouldNotTouch(PatientMonthlyBillingDTO $patient, string $code): bool
    {
        $locationServiceIsLocked = (bool) optional(collect($patient->getLocationServices())
            ->filter(fn (LocationChargeableServicesForProcessing $s) => $s->code === $code)
            ->first())
            ->isLocked;

        return $locationServiceIsLocked || $patient->billingStatusIsTouched();
    }
}
