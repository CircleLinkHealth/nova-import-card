<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\CcmBilling\Domain\Patient\PatientIsOfServiceCode;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\SharedModels\Entities\Problem;
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