<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientIsOfServiceCode;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Tests\CustomerTestCase;
use Tests\Helpers\Users\Patient\Problems;

class IsPcmTest extends CustomerTestCase
{
    use Problems;

    public function test_is_pcm()
    {
        $this->assertFalse(PatientIsOfServiceCode::execute($this->patient()->id, ChargeableService::PCM));

        /** @var Problem $problem */
        $problem = $this->attachValidPcmProblem($this->patient());

        $this->assertTrue(PatientIsOfServiceCode::execute($this->patient()->id, ChargeableService::PCM));
    }
}
