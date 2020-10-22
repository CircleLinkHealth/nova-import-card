<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use CircleLinkHealth\CcmBilling\Repositories\CachedLocationProcessorEloquentRepository;

class CachedLocationProcessorEloquentRepositoryTest extends LocationRepositoryTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedLocationProcessorEloquentRepository();
    }
}
