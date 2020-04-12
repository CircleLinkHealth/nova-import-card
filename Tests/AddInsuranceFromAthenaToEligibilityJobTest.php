<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeCalvaryCcda;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
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
        $athena = \Mockery::mock(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);
        
        $athena->shouldReceive('getPatientInsurances')
            ->once()
            ->andReturn($successfulApiResponse);
        
        $decorator = new InsuranceFromAthena($athena);

        //Setup
        $eligibilityJobInitialState = factory(EligibilityJob::class)->create();
        $ccda = FakeCalvaryCcda::create();
        $targetPatient = factory(TargetPatient::class)->create(['eligibility_job_id' => $eligibilityJobInitialState->id, 'ccda_id' => $ccda->id]);

        //Conduct Test
        $eligibilityJob = $decorator->decorate($eligibilityJobInitialState, $targetPatient, $ccda);

        //Assert
        $this->assertInstanceOf(EligibilityJob::class, $eligibilityJob);
        $this->assertEquals($successfulApiResponse['insurances'], $eligibilityJob->data['insurances']);
    }

//    public function test_it_successfully_processes_athena_target_patient()
//    {
//        //Setup
//        $athena = \Mockery::mock(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);
//
//        //setting up mock expectations
//        $athena->shouldReceive('getPatientInsurances')
//            ->once()
//            ->andReturn(AthenaApiResponses::getPatientInsurances())
//            ->shouldReceive('getCcd')
//            ->once()
//            ->andReturn(AthenaApiResponses::getCcd());
//
//        //and inject the mock into the container
//        $this->app->instance(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class, $athena);
//
//        $ccda = FakeCalvaryCcda::create();
//        $targetPatient = factory(TargetPatient::class)->create(['ccda_id' => $ccda->id]);
//
//        //Conduct Test
//        $eligibilityJob = $targetPatient->processEligibility();
//
//        //Assert
//        $this->assertInstanceOf(EligibilityJob::class, $eligibilityJob);
//        $this->assertEquals(AthenaApiResponses::getPatientInsurances()['insurances'], $eligibilityJob->data['insurances']);
//    }
}
