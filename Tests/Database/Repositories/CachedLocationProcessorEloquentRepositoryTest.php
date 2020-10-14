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

class CachedLocationProcessorEloquentRepositoryTest extends LocationRepositoryTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedLocationProcessorEloquentRepository();
    }
}
