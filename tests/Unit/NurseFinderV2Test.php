<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NurseFinder\NurseFinderContract;
use App\User;
use Mockery;
use Tests\TestCase;

class NurseFinderV2Test extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function test_patient_doesnt_have_associated_nurse()
    {
        $patientId = 2;

        //
        // MOCKS
        //
        $m = Mockery::mock(NurseFinderContract::class);

        //
        // EXPECTATIONS
        //
        $m->shouldReceive('find')
            ->with($patientId)
            ->once()
            ->andReturn(null);

        //
        // ACT OUT OUR LOGIC
        //
        $concr = new AFakeClassThatUsesNurseFinderV2($m);

        //
        // MAKE ASSERTIONS
        //
        $this->assertTrue(is_null($concr->iOnlyExistForTesting($patientId)));
    }

    public function test_patient_has_associated_nurse()
    {
        $patientId = 2;

        //
        // MOCKS
        //
        $m = Mockery::mock(NurseFinderContract::class);

        //
        // EXPECTATIONS
        //
        $m->shouldReceive('find')
            ->with($patientId)
            ->once()
            ->andReturn(new User());

        //
        // ACT OUT OUR LOGIC
        //
        $concr = new AFakeClassThatUsesNurseFinderV2($m);

        //
        // MAKE ASSERTIONS
        //
        $this->assertTrue($concr->iOnlyExistForTesting($patientId) instanceof User);
    }
}

class AFakeClassThatUsesNurseFinderV2
{
    public NurseFinderContract $nurseFinderV2;

    public function __construct(NurseFinderContract $nurseFinderV2)
    {
        $this->nurseFinderV2 = $nurseFinderV2;
    }

    public function iOnlyExistForTesting(int $patientId)
    {
        return $this->nurseFinderV2->find($patientId);
    }
}
