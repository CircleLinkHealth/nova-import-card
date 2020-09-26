<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class PatientBillingHelpersTest extends BillingTestCase
{
    public function test_patient_is_bhi_helper_uses_new_summaries()
    {
        $bhiCpmProblem = CpmProblem::hasChargeableServiceCodeForLocation($bhiCode = ChargeableService::BHI, ($location = $this->getLocation())->id)
            ->first();

        self::assertTrue(($patient = $this->patient())->ccdProblems->isEmpty());

        $patient->ccdProblems()->create([
            'name'           => str_random(8),
            'cpm_problem_id' => $bhiCpmProblem->id,
            'is_monitored'   => true,
        ]);

        self::assertTrue($location->chargeableServiceSummaries->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode($bhiCode))->isNotEmpty());

        ProcessSinglePatientMonthlyServices::dispatch($patient->id, Carbon::now()->startOfMonth());

        $patient->notes()->create([
            'type'      => Patient::BHI_CONSENT_NOTE_TYPE,
            'author_id' => $this->careCoach()->id,
        ]);

        self::assertTrue($patient->isBhi());
    }

    public function test_patient_is_pcm_helper_uses_new_summaries()
    {
        $pcmProblem = CpmProblem::hasChargeableServiceCodeForLocation($pcmCode = ChargeableService::PCM, ($location = $this->getLocation())->id)
            ->first();

        self::assertTrue(($patient = $this->patient())->ccdProblems->isEmpty());

        $patient->ccdProblems()->create([
            'name'           => str_random(8),
            'cpm_problem_id' => $pcmProblem->id,
            'is_monitored'   => true,
        ]);

        self::assertTrue($location->chargeableServiceSummaries->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode($pcmCode))->isNotEmpty());

        ProcessSinglePatientMonthlyServices::dispatch($patient->id, Carbon::now()->startOfMonth());

        self::assertTrue($patient->isPcm());
    }
}
