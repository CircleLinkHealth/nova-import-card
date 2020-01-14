<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\CheckCcdaEnrollmentEligibility;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Tests\TestCase;

class CheckCcdaEnrollmentEligibilityTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_it_eligibility_job_is_created_from_ccda_and_processed()
    {
        $practice = factory(Practice::class)->create();
        $batch    = factory(EligibilityBatch::class)->create(['type' => EligibilityBatch::ATHENA_API]);

        $ccda = Ccda::create([
            'practice_id' => $practice->id,
            'vendor_id'   => 1,
            'xml'         => file_get_contents(storage_path('ccdas/Samples/athenahealth-sample.xml')),
            'status'      => Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY,
            'source'      => Ccda::ATHENA_API,
            'batch_id'    => $batch->id,
        ]);

        $job   = new CheckCcdaEnrollmentEligibility($ccda, $practice, $batch);
        $check = $job->handle();

        $this->assertEquals(3, $check->getEligibilityJob()->status, '$check->getEligibilityJob()->status for EligibilityJob:'.$check->getEligibilityJob()->id);
    }
}
