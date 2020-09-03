<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use CircleLinkHealth\CcmBilling\Jobs\GenerateLocationSummaries;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Fake as FakeLocationRepository;
use Tests\TestCase;

class LocationSummaryProcessingTest extends TestCase
{
    public function test_it_generates_summaries_for_location_at_the_start_of_month()
    {
        FakeLocationRepository::fake();

//        GenerateLocationSummaries::dispatch(1, $month->addMonth(1)->startOfMonth());
//
//        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getCcm()->code(), $startOfMonth);
//        FakePatientRepository::assertChargeableSummaryCreated(1, $stub->getAvailableServiceProcessors()->getBhi()->code(), $startOfMonth);
    }
}
