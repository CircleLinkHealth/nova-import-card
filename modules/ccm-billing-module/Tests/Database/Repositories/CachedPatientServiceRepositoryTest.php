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
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Support\Facades\DB;

class CachedPatientServiceRepositoryTest extends PatientServiceRepositoryTest
{
    use PracticeHelpers;
    use UserHelpers;

    protected $location;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = new CachedPatientServiceProcessorRepository();
    }

    public function test_it_fetches_patient_from_cache_and_does_not_perform_multiple_queries_during_processing()
    {
        $patient = $this->setupPatientWithSummaries();

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
        $patient = $this->setupPatientWithSummaries();

        $serviceToAttach = ChargeableService::whereNotIn(
            'id',
            $this->repo->getChargeablePatientSummaries($patient->id, $thisMonth = Carbon::now()->startOfMonth())
                ->pluck('id')
        )->first();

        DB::enableQueryLog();

        $this->repo->store($patient->id, $serviceToAttach->code, $thisMonth);

        self::assertTrue(
            $this->repo->getChargeablePatientSummaries($patient->id, $thisMonth)
                ->where('chargeable_service_id', $serviceToAttach->id)
                ->isNotEmpty()
        );

        /*
         * We expect 5 calls to the DB at this point
         * 1. Get Chargeable Service Id using Code (normally it will be cached)
         * 2. ServiceSummaryModel::updateOrCreate initial select query to see if model exists
         * 3. ServiceSummaryModel::updateOrCreate subsequent insert query
         * 4. Revisions insertion after insert query
         * 5. ServiceSummarySQLView select query to update cached data
         */
        self::assertTrue(5 === count(DB::getQueryLog()));

        DB::disableQueryLog();
    }

    public function test_it_updates_cached_records_on_fulfill()
    {
        $patient = $this->setupPatientWithSummaries();

        $patientSummaries = $this->repo->getChargeablePatientSummaries($patient->id, $thisMonth = Carbon::now()->startOfMonth());

        DB::enableQueryLog();

        $this->repo->fulfill(
            $patient->id,
            $patientSummaries->first()->chargeable_service_code,
            $thisMonth
        );

        /*
         * We expect 5 calls to the DB at this point
         * Get Chargeable Service Id using Code has already been called on processing and it's cached
         * 1. ServiceSummaryModel::updateOrCreate initial select query to see if model exists
         * 2. ServiceSummaryModel::updateOrCreate subsequent insert query
         * 3. Revisions insertion after insert query
         *
         */
        self::assertTrue(3 === count(DB::getQueryLog()));

        DB::disableQueryLog();
    }

    public function test_it_updates_cached_records_on_set_consent()
    {
    }

    private function setupPatientWithSummaries(): User
    {
        return $this->setupPatient(
            $this->setupPractice(true, true, true, true),
            true
        );
    }
}
