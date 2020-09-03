<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Fake as FakeLocationRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs\ChargeableLocationMonthlySummaryStub;
use Tests\TestCase;

class LocationSummaryProcessingTest extends TestCase
{
    public function test_it_copies_auto_attaches_services_from_other_practice_location_if_new()
    {
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

    public function test_location_observer_triggers_location_processing()
    {
        //fake observer event?
    }
}
