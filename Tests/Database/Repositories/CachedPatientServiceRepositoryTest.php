<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use CircleLinkHealth\CcmBilling\Repositories\CachedPatientServiceProcessorRepository;

class CachedPatientServiceRepositoryTest extends PatientServiceRepositoryTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedPatientServiceProcessorRepository();
    }

    public function test_it_fetches_patient_from_cache_and_does_not_perform_multiple_queries()
    {
    }

    public function test_it_updates_cached_records_on_attach()
    {
    }

    public function test_it_updates_cached_records_on_fulfill()
    {
    }

    public function test_it_updates_cached_records_on_set_consent()
    {
    }
}
