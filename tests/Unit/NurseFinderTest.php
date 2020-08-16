<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NextCallCalculator\Handlers\SuccessfulHandler;
use App\Algorithms\Calls\NextCallCalculator\NextCallDateCalculator;
use App\Algorithms\Calls\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\AppConfig\StandByNurseUser;
use CircleLinkHealth\Customer\Entities\User;
use Mockery;
use Tests\TestCase;

class NurseFinderTest extends TestCase
{
    public function test_it_returns_patient_associated_nurse()
    {
        $patient = factory(User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $nurse = factory(User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $repo = Mockery::mock(NurseFinderEloquentRepository::class);

        $repo->shouldReceive('find')
            ->with($patient->id)
            ->once()
            ->andReturn($nurse);

        $this->instance(NurseFinderEloquentRepository::class, $repo);

        $prediction = (new NextCallDateCalculator())->handle($patient, new SuccessfulHandler());

        $this->assertTrue($prediction->nurse === $nurse->id);
    }

    public function test_it_returns_standby_nurse_if_patient_doesnt_have_associated_nurse_and_standby_nurse_is_set()
    {
        $patient = factory(User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $nurse = factory(User::class)->make([
            'id'           => 123456789,
            'display_name' => 'Soulla Masoulla',
        ]);
        $this->mock(StandByNurseUser::class, function ($mock) use ($nurse) {
            $mock->shouldReceive('user')->atLeast(1)->andReturn($nurse);
        });

        $prediction = (new NextCallDateCalculator())->handle($patient, new SuccessfulHandler());

        $this->assertTrue($prediction->nurse === $nurse->id);
    }

    public function test_nurse_is_null_if_patient_doesnt_have_associated_nurse_and_standby_nurse_is_not_set()
    {
        $patient = factory(User::class)->make([
            'id' => rand(1, 9999999),
        ]);
        $repo = Mockery::mock(NurseFinderEloquentRepository::class);

        $repo->shouldReceive('find')
            ->with($patient->id)
            ->once()
            ->andReturn(null);

        $this->instance(NurseFinderEloquentRepository::class, $repo);

        $prediction = (new NextCallDateCalculator())->handle($patient, new SuccessfulHandler());

        $this->assertTrue(is_null($prediction->nurse));
    }
}
