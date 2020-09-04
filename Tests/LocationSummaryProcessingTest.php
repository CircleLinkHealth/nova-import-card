<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientMonthlyServices;
use CircleLinkHealth\CcmBilling\Jobs\ProcessLocationPatientsChunk;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Fake as FakeLocationRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs\ChargeableLocationMonthlySummaryStub;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Tests\TestCase;

class LocationSummaryProcessingTest extends TestCase
{
    public function test_event_dispatches_job_to_process_location_patient_summaries()
    {
        Bus::fake();

        event(new LocationServicesAttached($locationId = 1));

        Bus::assertDispatched(function (ProcessLocationPatientMonthlyServices $job) use ($locationId) {
            return $locationId === $job->getLocationId() && $job->getChargeableMonth()->equalTo(Carbon::now()->startOfMonth()->startOfDay());
        });
    }

    public function test_it_processes_patient_summaries_on_location_summary_changes()
    {
        //fake location available processors
        //fake patient stubs
        //write mockery test for this?

//        $monthYear = Carbon::now()->startOfMonth();
//
//
//
//        $repoMock    = Mockery::mock(LocationProcessorEloquentRepository::class);
//        $builderMock = Mockery::mock(Builder::class);
//
//
//
//        //check event is dispatched - no
//        //MOCK USER->GET PROBLEM CODES and return collection with CS codes.
//
//        //assert location job dispatch at event
//        //assert chunk job dispatched from initial job
//        //assert patient Jobs dispatched
//        //maybe different test for each one
//
    }

    public function test_it_renews_summaries_for_location_at_the_start_of_month()
    {
        FakeLocationRepository::fake();

        FakeLocationRepository::setChargeableLocationMonthlySummaryStubs(
            (new ChargeableLocationMonthlySummaryStub())
                ->setLocationId($locationId = 1)
                ->setChargeableServiceId($cs1 = 1)
                ->setChargeableMonth($pastMonth = Carbon::now()->subMonth()->startOfMonth())
                ->setAmount($amount1 = floatval(20.00)),
            (new ChargeableLocationMonthlySummaryStub())
                ->setLocationId($locationId)
                ->setChargeableServiceId($cs2 = 2)
                ->setChargeableMonth($pastMonth)
                ->setAmount($amount2 = floatval(25.50)),
            (new ChargeableLocationMonthlySummaryStub())
                ->setLocationId($locationId)
                ->setChargeableServiceId($cs3 = 3)
                ->setChargeableMonth($pastMonth)
        );

        $processor = app(Location::class);
        $processor->processServicesForLocation($locationId, $startOfMonth = Carbon::now()->startOfMonth());

        FakeLocationRepository::assertChargeableSummaryCreated($locationId, $cs1, $startOfMonth, $amount1);
        FakeLocationRepository::assertChargeableSummaryCreated($locationId, $cs2, $startOfMonth, $amount2);
        FakeLocationRepository::assertChargeableSummaryCreated($locationId, $cs3, $startOfMonth);
        FakeLocationRepository::assertChargeableSummaryNotCreated($locationId, $cs4 = 4, $startOfMonth);

        FakeLocationRepository::store($locationId, $cs4, $pastMonth);

        $processor->processServicesForLocation($locationId, $startOfMonth);

        FakeLocationRepository::assertChargeableSummaryNotCreated($locationId, $cs4, $startOfMonth);
    }

    public function test_location_processor_chunks_into_jobs()
    {
        Bus::fake();
        FakeLocationRepository::fake();

        FakeLocationRepository::setLocationProcessors(
            $locationId = 1,
            $startOfMonth = Carbon::now()->startOfMonth()->startOfDay(),
            AvailableServiceProcessors::push(
                [
                    new CCM(),
                    new BHI(),
                    new PCM(),
                ]
            )
        );

        $builderMock = Mockery::mock(Builder::class);

        $builderMock
            ->shouldReceive('count')
            ->andReturn($chunkSizeUsedByProcessor = 100 * $jobsExpectedToDispatch = 10);

        $builderMock->shouldReceive('offset')
            ->andReturnSelf();

        $builderMock->shouldReceive('limit')
            ->andReturnSelf();

        $builderMock->makePartial();

        FakeLocationRepository::setBuilder($builderMock);

        app(Location::class)->processServicesForAllPatients($locationId, $startOfMonth);

        Bus::assertDispatched(function (ProcessLocationPatientsChunk $job) use ($startOfMonth) {
            $availableProcessors = $job->getAvailableServiceProcessors();

            return $job->getChargeableMonth()->equalTo($startOfMonth)
                && ! is_null($bhiProcessor = $availableProcessors->getBhi())
                && is_a($bhiProcessor, BHI::class)
                && ! is_null($ccmProcessor = $availableProcessors->getCcm())
                && is_a($ccmProcessor, CCM::class)
                && ! is_null($pcmProcessor = $availableProcessors->getPcm())
                && is_a($pcmProcessor, PCM::class)
                && is_null($availableProcessors->getAwv1())
                && is_null($availableProcessors->getAwv2())
                && is_null($availableProcessors->getCcm40())
                && is_null($availableProcessors->getCcm60());
        });

        Bus::assertDispatchedTimes(ProcessLocationPatientsChunk::class, $jobsExpectedToDispatch);
    }
}
