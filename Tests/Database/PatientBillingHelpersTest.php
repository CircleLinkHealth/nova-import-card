<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;

class PatientBillingHelpersTest extends BillingTestCase
{
    public function test_patient_is_bhi_helper_uses_new_summaries()
    {
        self::assertTrue(($patient = $this->patient())->ccdProblems->isEmpty());

        $this->createPatientCcdProblemOfCode($patient = $this->patient(), $bhiCode = ChargeableService::BHI);

        self::assertTrue(
            $this->getLocation()
                ->chargeableServiceSummaries
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode($bhiCode))
                ->isNotEmpty()
        );

        $patient->notes()->create([
            'type'      => Patient::BHI_CONSENT_NOTE_TYPE,
            'author_id' => $this->careCoach()->id,
        ]);

        ProcessSinglePatientMonthlyServices::dispatch($patient->id, Carbon::now()->startOfMonth());

        self::assertTrue($patient->isBhi());
    }

    public function test_patient_is_pcm_helper_uses_new_summaries()
    {
        self::assertTrue(($patient = $this->patient())->ccdProblems->isEmpty());

        $this->createPatientCcdProblemOfCode($patient = $this->patient(), $pcmCode = ChargeableService::PCM);

        self::assertTrue(
            $this->getLocation()
                ->chargeableServiceSummaries
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode($pcmCode))
                ->isNotEmpty()
        );

        ProcessSinglePatientMonthlyServices::dispatch($patient->id, Carbon::now()->startOfMonth());

        self::assertTrue($patient->isPcm());
    }
}
