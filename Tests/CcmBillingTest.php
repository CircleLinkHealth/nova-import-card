<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatient;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Modules\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use Tests\TestCase;
use Mockery;

class CcmBillingTest extends TestCase
{
    public function test_it_fetches_approvable_patients_for_location()
    {
        $locationId = 5;
        $monthYear  = now()->startOfMonth();
        $pageSize   = 100;
        $fakeUsers  = factory(User::class, 5)->make()->transform(fn ($user) => new ApprovablePatient($user));

        $repoMock      = Mockery::mock(LocationProcessorEloquentRepository::class);
        $paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $paginatorMock->shouldReceive('first')->andReturn(false);
        $paginatorMock->shouldReceive('mapInto')->with(ApprovablePatient::class)->once()->andReturn($fakeUsers);

        $repoMock
            ->shouldReceive('patients')
            ->with($locationId, $monthYear, $pageSize)
            ->once()
            ->andReturn($paginatorMock);

        $biller = new Location($repoMock);

        $response = $biller->fetchApprovablePatients($locationId, $monthYear, $pageSize);
        $this->assertTrue($response instanceof ApprovablePatientCollection);
    }
}
