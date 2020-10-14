<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Processors\Patient\BHI;
use CircleLinkHealth\CcmBilling\Processors\Patient\CCM;
use CircleLinkHealth\CcmBilling\Processors\Patient\PCM;
use CircleLinkHealth\CcmBilling\Repositories\CachedLocationProcessorEloquentRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use Tests\CustomerTestCase;

class CachedLocationProcessorEloquentRepositoryTest extends CustomerTestCase
{
    protected CachedLocationProcessorEloquentRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedLocationProcessorEloquentRepository();
    }

    public function test_it_brings_the_right_processors_for_month()
    {
        $location     = factory(Location::class)->create();
        $startOfMonth = Carbon::now()->startOfMonth();
        foreach ([
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::PCM,
        ] as  $code) {
            $this->repo->store($location->id, $code, $startOfMonth);
        }

        $processors = $this->repo->availableLocationServiceProcessors($location->id, $startOfMonth);

        self::assertNotNull($processors->getCcm());
        self::assertTrue(is_a($processors->getCcm(), CCM::class));
        self::assertNotNull($processors->getBhi());
        self::assertTrue(is_a($processors->getBhi(), BHI::class));
        self::assertNotNull($processors->getPcm());
        self::assertTrue(is_a($processors->getPcm(), PCM::class));

        self::assertNull($processors->getCcm40());
        self::assertNull($processors->getCcm60());
        self::assertNull($processors->getAwv1());
        self::assertNull($processors->getAwv2());
    }
}
