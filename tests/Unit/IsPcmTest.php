<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Tests\CustomerTestCase;
use Tests\Helpers\Users\Patient\Problems;

class IsPcmTest extends CustomerTestCase
{
    use Problems;

    public function test_is_pcm()
    {
        $this->assertFalse($this->patient()->isPcm());

        /** @var Problem $problem */
        $problem = $this->attachValidPcmProblem($this->patient());

        $this->assertTrue($this->patient()->isPcm());

        PcmProblem::where('code', $problem->icd10Code())->delete();

        $this->assertFalse($this->patient()->isPcm());
    }
}
