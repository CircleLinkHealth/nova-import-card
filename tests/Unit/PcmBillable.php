<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Services\ApproveBillablePatientsService;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Tests\CustomerTestCase;
use Tests\Helpers\Users\Patient\Problems;

class PcmBillable extends CustomerTestCase
{
    use PracticeHelpers;
    use Problems;
    use TimeHelpers;
    use UserHelpers;

    /**
     * Patient with 2 problems from practice that has both CCM and PCM enabled.
     * Should bill practice for CCM.
     *
     * @return void
     */
    public function test_practice_billed_for_ccm_because_more_than_one_problem()
    {
        $practice = $this->setupPractice(true, false, false, true);
        $patient  = $this->setupPatient($practice, false, false);
        $this->attachValidPcmProblem($patient);

        $nurse = $this->getNurse($practice->id, true, 1, true, 12.50);
        $this->addTime($nurse, $patient, 31, true, true, false);

        $service        = app(ApproveBillablePatientsService::class);
        $month          = $service->getBillablePatientsForMonth($practice->id, now());
        $patientSummary = $month['summaries']->first();
        $this->assertEquals(1, $patientSummary->chargeableServices->count());
        $this->assertEquals(ChargeableService::CCM, $patientSummary->chargeableServices->first()->code);
    }

    /**
     * Patient with 2 problems from practice that has PCM enabled.
     * Should bill practice for PCM.
     *
     * @return void
     */
    public function test_practice_billed_for_pcm()
    {
        $practice = $this->setupPractice(false, false, false, true);
        $patient  = $this->setupPatient($practice, false, false);
        $this->attachValidPcmProblem($patient);

        $nurse = $this->getNurse($practice->id, true, 1, true, 12.50);
        $this->addTime($nurse, $patient, 31, true, true, false);

        $service        = app(ApproveBillablePatientsService::class);
        $month          = $service->getBillablePatientsForMonth($practice->id, now());
        $patientSummary = $month['summaries']->first();
        $this->assertEquals(1, $patientSummary->chargeableServices->count());
        $this->assertEquals(ChargeableService::PCM, $patientSummary->chargeableServices->first()->code);
    }

    private function getNurse(
        $practiceId,
        bool $variableRate = true,
        float $hourlyRate = 29.0,
        bool $enableCcmPlus = false,
        float $visitFee = null
    ) {
        $nurse = $this->createUser($practiceId, 'care-center');

        return $this->setupNurse($nurse, $variableRate, $hourlyRate, $enableCcmPlus, $visitFee);
    }
}
