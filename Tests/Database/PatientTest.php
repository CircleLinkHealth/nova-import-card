<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\TimeTracking\Entities\Activity;
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
        self::assertNotNull(
            $summary = $this->repo->store(
                $patientId = $this->patient()->id,
                $ccmCode = ChargeableService::CCM,
                $month = Carbon::now()->startOfMonth()
            )
        );
        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));
        self::assertTrue($this->repo->isAttached($patientId, $ccmCode, $month));
    }

    public function test_patient_chargeable_summary_relationships()
    {
        $this->patient()->chargeableMonthlySummaries()->create([
            'chargeable_service_id' => $ccmCodeId = $this->repo->chargeableSercviceId($ccmCode = ChargeableService::CCM),
            'chargeable_month'      => $month = Carbon::now()->startOfMonth(),
        ]);

        self::assertNotNull(
            $this->patient()->chargeableMonthlySummaries()
                ->where('chargeable_service_id', $ccmCodeId)
                ->where('chargeable_month', $month)
                ->first()
        );

        self::assertNotNull(
            $this->patient()->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', $ccmCode)
                ->where('chargeable_month', $month)
                ->first()
        );
    }

    public function test_patient_summary_sql_view_has_correct_auxiliary_metrics()
    {
        self::assertNotNull(
            $summary = $this->repo->store(
                $patientId = $this->patient()->id,
                $ccmCode = ChargeableService::CCM,
                $month = Carbon::now()->startOfMonth()
            )
        );

        Call::insert([
            [
                'inbound_cpm_id'  => $patientId,
                'outbound_cpm_id' => $careCoachId = $this->careCoach()->id,
                'type'            => 'call',
                'status'          => Call::REACHED,
                'called_date'     => Carbon::now()->startOfMonth()->addDay(10),
            ],
            [
                'inbound_cpm_id'  => $patientId,
                'outbound_cpm_id' => $careCoachId,
                'type'            => 'call',
                'status'          => Call::NOT_REACHED,
                'called_date'     => Carbon::now()->startOfMonth()->addDay(5),
            ],
        ]);

        Activity::insert([
            [
                'duration'              => $duration1 = 50,
                'patient_id'            => $patientId,
                'provider_id'           => $careCoachId,
                'chargeable_service_id' => $ccmCodeId = $this->repo->chargeableSercviceId($ccmCode),
                'performed_at'          => Carbon::now()->startOfMonth()->addDay(7),
            ],
            [
                'duration'              => $duration2 = 100,
                'patient_id'            => $patientId,
                'provider_id'           => $careCoachId,
                'chargeable_service_id' => $ccmCodeId,
                'performed_at'          => Carbon::now()->startOfMonth()->addDay(14),
            ],
        ]);

        self::assertNotNull(
            $viewSummary = $this->patient()->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', $ccmCode)
                ->where('chargeable_month', $month)
                ->first()
        );

        self::assertEquals($viewSummary->no_of_calls, 2);
        self::assertEquals($viewSummary->no_of_successful_calls, 1);

        self::assertEquals($viewSummary->total_time, $duration1 + $duration2);
    }
    
    public function test_patient_can_have_fulfilled_chargeable_monthly_summaries_attached(){
        self::assertNotNull(
            $summary = $this->repo->fulfill(
                $patientId = $this->patient()->id,
                $ccmCode = ChargeableService::CCM,
                $month = Carbon::now()->startOfMonth()
            )
        );
        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));
        self::assertTrue($this->repo->isFulfilled($patientId, $ccmCode, $month));
    }
    
    public function test_patient_can_have_bhi_summary_that_needs_consent(){
        self::assertNotNull(
            $summary = $this->repo->store(
                $patientId = $this->patient()->id,
                $ccmCode = ChargeableService::CCM,
                $month = Carbon::now()->startOfMonth(),
                true
            )
        );
        self::assertTrue(is_a($summary, ChargeablePatientMonthlySummary::class));
        self::assertTrue($this->repo->requiresPatientConsent($patientId, $ccmCode, $month));
    
        $this->repo->setPatientConsented($patientId, $ccmCode, $month);
        self::assertFalse($this->repo->requiresPatientConsent($patientId, $ccmCode, $month));
    }
    
    public function test_patient_can_have_end_of_month_ccm_status_log(){
    
    }
}
