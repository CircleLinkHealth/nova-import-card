<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Tests\CustomerTestCase;

class PatientTest extends CustomerTestCase
{
    protected PatientServiceProcessorRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new PatientServiceProcessorRepository();
    }

    public function test_patient_can_have_summaries_for_each_service_for_each_month()
    {
        $patient = $this->patient();

        $summary = $this->repo->store($patient->id, $ccmCode = ChargeableService::CCM, $month = Carbon::now()->startOfMonth());

        self::assertNotNull($summary);
        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));
        self::assertTrue($this->repo->isAttached($patient->id, $ccmCode, $month));
    }

    public function test_patient_summary_sql_view_has_correct_auxiliary_metrics()
    {
        //do not process
        //attach
        //create activities
        //create calls
        //assert view has the correct data
    }
}
