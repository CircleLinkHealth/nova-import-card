<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Tests\CustomerTestCase;

class LocationTest extends CustomerTestCase
{
    protected LocationProcessorEloquentRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new LocationProcessorEloquentRepository();
    }

    public function test_location_can_have_summaries_for_each_service_for_each_month()
    {
        self::assertNotNull(
            $summary = $this->repo->store(
                $locationId = $this->location()->id,
                $ccmCode = ChargeableService::CCM,
                $month = Carbon::now()->startOfMonth()
            )
        );
        self::assertTrue(is_a($summary, ChargeableLocationMonthlySummary::class));
        //todo: add bool method
        self::assertTrue(
            $this->repo->servicesForMonth($locationId, $month)
                ->whereHas('chargeableService', function ($cs) use ($ccmCode) {
                    $cs->where('code', $ccmCode);
                })
                ->exists()
        );
    }
}
