<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
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

    public function test_it_fetches_patient_summaries_sql_view_collection()
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
    }
}
