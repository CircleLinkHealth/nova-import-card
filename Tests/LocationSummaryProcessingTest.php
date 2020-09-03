<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Fake as FakeLocationRepository;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs\ChargeableLocationMonthlySummaryStub;
use Tests\TestCase;

class LocationSummaryProcessingTest extends TestCase
{
    public function test_it_renews_summaries_for_location_at_the_start_of_month()
    {
        FakeLocationRepository::fake();

        FakeLocationRepository::setChargeableLocationMonthlySummaryStubs(
            new ChargeableLocationMonthlySummaryStub(1, 1, Carbon::now()->subMonth()->startOfMonth(), floatval(20.00))
        );

        app(Location::class)->processServicesForLocation(1, $startOfMonth = Carbon::now()->startOfMonth());

//        GenerateLocationSummaries::dispatch(1, $month->addMonth(1)->startOfMonth());
//

        FakeLocationRepository::assertChargeableSummaryCreated(1, 1, $startOfMonth, floatval(20.00));
    }
}
