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
        //Mock
        $successfulApiResponse = AthenaApiResponses::getPatientInsurances();

        //I want to mock AthenaApiImplementation
        $athena = \Mockery::mock(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);
        //Here is what should happen
        $athena->shouldReceive('getPatientInsurances')
            ->once()
            ->andReturn($successfulApiResponse);
        //I will inject my mock in my class using DependencyInjection (DI)
        $decorator = new \CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob($athena);

        //Setup
        $eligibilityJobInitialState = factory(EligibilityJob::class)->create();
        $targetPatient              = new TargetPatient();
        $ccda                       = new Ccda();

        //Conduct Test
        $eligibilityJob = $decorator->addInsurancesFromAthena($eligibilityJobInitialState, $targetPatient, $ccda);

        //Assert
        $this->assertInstanceOf(EligibilityJob::class, $eligibilityJob);
        $this->assertEquals($successfulApiResponse['insurances'], $eligibilityJob->data['insurances']);
    }
}
