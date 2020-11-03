<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientProblemsForBillingProcessing;
use CircleLinkHealth\CcmBilling\Domain\Patient\ProcessPatientSummaries;
use CircleLinkHealth\CcmBilling\Repositories\CachedPatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Support\Facades\DB;

class CachedPatientServiceRepositoryTest extends PatientServiceRepositoryTest
{
    use UserHelpers;
    use PracticeHelpers;
    
    protected $location;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedPatientServiceProcessorRepository();
    }

    public function test_it_fetches_patient_from_cache_and_does_not_perform_multiple_queries_during_processing()
    {
        $practice = $this->setupPractice(true, true, true, true);
        $patient = $this->setupPatient($practice, true);
        
        $dto = (new PatientMonthlyBillingDTO())
            ->subscribe(
                (app(LocationProcessorRepository::class))
                    ->availableLocationServiceProcessors(
                        $patient->getPreferredContactLocation(),
                        $thisMonth = Carbon::now()->startOfMonth()
                    )
            )
            ->forPatient($patient->id)
            ->forMonth($thisMonth)
            ->withProblems(...PatientProblemsForBillingProcessing::getArray($patient->id));
        
        self::assertFalse(empty($dto->getAvailableServiceProcessors()));
        self::assertFalse(empty($dto->getPatientProblems()));
        
        DB::enableQueryLog();
        
        ($actionClass = app(ProcessPatientSummaries::class))->fromDTO($dto);
        
        self::assertTrue(empty(DB::getQueryLog()));
        
        $actionClass->execute($patient->id, $thisMonth);
    
        self::assertTrue(empty(DB::getQueryLog()));
        
        DB::disableQueryLog();
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
