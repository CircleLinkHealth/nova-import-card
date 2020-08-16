<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NextCallCalculator\Handlers\SuccessfulHandler;
use App\Algorithms\Calls\NextCallCalculator\NextCallDateCalculator;
use App\Algorithms\Calls\NurseFinderRepository;
use Mockery;
use Tests\TestCase;

class NurseFinderV2Test extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function test_patient_doesnt_have_associated_nurse_and_standby_nurse_is_not_set()
    {
        $patient = factory(\CircleLinkHealth\Customer\Entities\User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $repo = Mockery::mock(NurseFinderRepository::class);

        $repo->shouldReceive('find')
            ->with($patient->id)
            ->once()
            ->andReturn(null);

        $this->instance(NurseFinderRepository::class, $repo);

        $prediction = (new NextCallDateCalculator())->handle($patient, new SuccessfulHandler());

        $this->assertTrue(is_null($prediction->nurse));
    }

    public function test_patient_has_associated_nurse()
    {
        $patient = factory(\CircleLinkHealth\Customer\Entities\User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $nurse = factory(\CircleLinkHealth\Customer\Entities\User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $repo = Mockery::mock(NurseFinderRepository::class);

        $repo->shouldReceive('find')
            ->with($patient->id)
            ->once()
            ->andReturn($nurse);

        $this->instance(NurseFinderRepository::class, $repo);

        $prediction = (new NextCallDateCalculator())->handle($patient, new SuccessfulHandler());

        $this->assertTrue(
            $prediction->nurse
            === $nurse->id
        );
    }
}
