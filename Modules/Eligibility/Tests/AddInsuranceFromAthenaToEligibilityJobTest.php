<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use App\EligibilityJob;
use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use CircleLinkHealth\Eligibility\Tests\Fakers\AthenaApiResponses;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class AddInsuranceFromAthenaToEligibilityJobTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * A basic test example.
     */
    public function test_it_adds_insurance_from_athena()
    {
        $successfulApiResponse = AthenaApiResponses::getPatientInsurances();
        //Mock
        $athena = \Mockery::mock(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);
        $athena->shouldReceive('getPatientInsurances')->once()->andReturn($successfulApiResponse);

        //Test
        $eligibilityJob = factory(EligibilityJob::class)->create();
        $targetPatient  = new TargetPatient();
        $ccda           = new Ccda();
        $decorator      = new \CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob($athena);

        //Assert
        $eligibilityJob = $decorator->addInsurancesFromAthena($eligibilityJob, $targetPatient, $ccda);

        $this->assertInstanceOf(EligibilityJob::class, $eligibilityJob);
        $this->assertEquals($successfulApiResponse, $eligibilityJob->data['insurances']);
    }
}
