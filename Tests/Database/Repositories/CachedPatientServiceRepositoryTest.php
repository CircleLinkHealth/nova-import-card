<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\Repositories\CachedPatientServiceProcessorRepository;
use Illuminate\Support\Facades\DB;

class CachedPatientServiceRepositoryTest extends PatientServiceRepositoryTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedPatientServiceProcessorRepository();
    }

    public function test_it_fetches_patient_from_cache_and_does_not_perform_multiple_queries()
    {
        $patient = $this->patient();
        DB::enableQueryLog();
        app(ProcessPatientSummaries::class)->execute($patient->id, Carbon::now()->startOfMonth());
        $log = DB::getQueryLog();
        $x = 1;
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
