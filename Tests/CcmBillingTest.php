<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatient;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PracticeProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class BillingProcessorsIntegrationTest extends TestCase
{
    public function test_it_fetches_approvable_patients_for_location()
    {
        $locationId = 5;
        $monthYear  = now()->startOfMonth();
        $pageSize   = 100;
        $fakeUsers  = factory(User::class, 5)->make()->transform(fn ($user) => new ApprovablePatient($user));

        $repoMock      = Mockery::mock(LocationProcessorEloquentRepository::class);
        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $paginatorMock
            ->shouldReceive('first')
            ->andReturn(false);
        $paginatorMock->shouldReceive('mapInto')
            ->with(ApprovablePatient::class)
            ->once()
            ->andReturn($fakeUsers);
        $repoMock
            ->shouldReceive('patients')
            ->with($locationId, $monthYear, $pageSize)
            ->once()
            ->andReturn($paginatorMock);

        $biller   = new Location($repoMock);
        $response = $biller->fetchApprovablePatients($locationId, $monthYear, $pageSize);

        $this->assertTrue($response instanceof ApprovablePatientCollection);
        $this->assertTrue($response->collection->count() === $fakeUsers->count());
    }

    public function test_it_fetches_approvable_patients_for_practice()
    {
        $practiceId = 5;
        $monthYear  = now()->startOfMonth();
        $pageSize   = 100;
        $fakeUsers  = factory(User::class, 5)->make()->transform(fn ($user) => new ApprovablePatient($user));

        $repoMock      = Mockery::mock(PracticeProcessorEloquentRepository::class);
        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $paginatorMock
            ->shouldReceive('first')
            ->andReturn(false);
        $paginatorMock->shouldReceive('mapInto')
            ->with(ApprovablePatient::class)
            ->once()
            ->andReturn($fakeUsers);
        $repoMock
            ->shouldReceive('patients')
            ->with($practiceId, $monthYear, $pageSize)
            ->once()
            ->andReturn($paginatorMock);

        $biller   = new Practice($repoMock);
        $response = $biller->fetchApprovablePatients($practiceId, $monthYear, $pageSize);

        $this->assertTrue($response instanceof ApprovablePatientCollection);
        $this->assertTrue($response->collection->count() === $fakeUsers->count());
    }
}
