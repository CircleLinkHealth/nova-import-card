<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Tests\CustomerTestCase;

class PatientServiceRepositoryTest extends CustomerTestCase
{
    protected PatientServiceProcessorRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new PatientServiceProcessorRepository();
    }

    public function test_if_it_checks_if_service_is_available_for_location()
    {
        (new LocationProcessorEloquentRepository())->store(
            $locationId = $this->patient()->getPreferredContactLocation(),
            $ccmCode = ChargeableService::CCM,
            $startOfMonth = Carbon::now()->startOfMonth()
        );

        self::assertTrue($this->repo->isChargeableServiceEnabledForLocationForMonth($this->patient()->id, $ccmCode, $startOfMonth));
        self::assertFalse($this->repo->isChargeableServiceEnabledForLocationForMonth($this->patient()->id, ChargeableService::BHI, $startOfMonth));
    }

    public function test_it_can_require_and_set_patient_consent()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());
        self::assertFalse($this->repo->requiresPatientConsent($patientId, $ccmCode, $startOfMonth));

        $this->repo->store($patientId, $bhiCode = ChargeableService::BHI, $startOfMonth, true);
        self::assertTrue($this->repo->requiresPatientConsent($patientId, $bhiCode, $startOfMonth));

        $this->repo->setPatientConsented($patientId, $bhiCode, $startOfMonth);
        self::assertFalse($this->repo->requiresPatientConsent($patientId, $bhiCode, $startOfMonth));
    }

    //todo: do we need an extended Customer Test case? an extended test case would include ability to pull month, all kinds of repos etc.
    public function test_it_checks_if_summary_is_attached()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());

        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $startOfMonth));
        self::assertFalse($this->repo->isAttached($patientId, ChargeableService::BHI, $startOfMonth));
    }

    public function test_it_fetches_patient_summaries_sql_view()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        foreach ([
            $ccmCode = ChargeableService::CCM,
            $bhiCode = ChargeableService::BHI,
        ] as $code) {
            $this->repo->store($this->patient()->id, $code, $startOfMonth);
        }

        self::assertTrue(($summaries = $this->repo->getChargeablePatientSummaries($this->patient()->id, $startOfMonth))->isNotEmpty());
        self::assertTrue(2 === $summaries->count());

        self::assertNotNull($ccmSummary = $summaries->where('chargeable_service_code', $ccmCode)->where('chargeable_month', $startOfMonth)->first());
        self::assertNotNull($bhiSummary = $summaries->where('chargeable_service_code', $bhiCode)->where('chargeable_month', $startOfMonth)->first());
        self::assertTrue(is_a($ccmSummary, ChargeablePatientMonthlySummaryView::class));
        self::assertTrue(is_a($bhiSummary, ChargeablePatientMonthlySummaryView::class));

        self::assertNotNull($summary = $this->repo->getChargeablePatientSummary($this->patient()->id, $ccmCode, $startOfMonth));
        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummaryView::class));
    }

    public function test_it_fulfills_summary()
    {
        $this->repo->store($patientId = $this->patient()->id, $ccmCode = ChargeableService::CCM, $startOfMonth = Carbon::now()->startOfMonth());

        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $startOfMonth));

        self::assertFalse($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));

        $summary = $this->repo->fulfill($patientId, $ccmCode, $startOfMonth);

        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));

        self::assertTrue($this->repo->isFulfilled($patientId, $ccmCode, $startOfMonth));
    }
}
