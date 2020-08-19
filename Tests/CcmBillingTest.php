<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatient;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovablePatientCollection;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PracticeProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\FakeMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Fake as FakePatientRepository;

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

    public function test_it_processes_patient_chargeable_services_at_the_start_of_month()
    {
        //for a given Patient User
        //of  a given Practice
        //of a specific preferred Location

        //attempt to process CS for the current (on the start of) month
        //1. take user problems
        //2. take Location enable CS? (QUESTION)
        //3. (4.)Call Patient CS Processors individually
        //    a) BHI->attach check should attach, return bool
        //    b) Is fulfilled - it would probably deduce/return false

        //mock to job to recieve patient and month
        // X determines current CSs/summary
        //

        // WE NEED PATIENT REPOSITORY
        // PATIENT PROCESSOR - returns collection of Classes (CS individual processors) ->getChargeableServicesForMonth()
        // ccdProblems.cpmProblems(.chargeableServices)
        // locationChargeableService
        
        FakePatientRepository::fake();
    
        $patient = factory(User::class)->make();
        
        $availableCs = new AvailableServiceProcessors();
        
        
        $availableCs->setCcm( $ccmProcessor = new CCM());
        $availableCs->setBhi( $bhiProcessor = new BHI());

        $stub = new PatientMonthlyBillingStub();
        $stub->setAvailableServiceProcessors($availableCs);
        
        $stub->setChargeableMonth($startOfMonth = Carbon::now()->startOfMonth()->startOfDay());
        
        //array will contain chargeable name with cpm problem id
        $stub->setPatientProblems(collect([]));
        $stub->setPatientId(1);

        $fakeProcessor = new MonthlyProcessor();

        $fakeProcessor->process($stub);
        
        FakePatientRepository::assertChargeableSummaryCreated(1, $ccmProcessor->code(), $startOfMonth);
        FakePatientRepository::assertChargeableSummaryCreated(1, $bhiProcessor->code(), $startOfMonth);
    }
}
