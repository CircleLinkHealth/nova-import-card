<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Database\Seeders\CpmProblemChargeableServiceLocationSeeder;
use CircleLinkHealth\CcmBilling\Domain\Patient\LogPatientCcmStatusForEndOfMonth;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
use CircleLinkHealth\CcmBilling\Jobs\SeedPracticeCpmProblemChargeableServicesFromLegacyTables;
use CircleLinkHealth\CcmBilling\Processors\Patient\MonthlyProcessor;
use CircleLinkHealth\CcmBilling\Repositories\LocationProblemServiceRepository;
use CircleLinkHealth\CcmBilling\Repositories\LocationProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientProcessorEloquentRepository;
use CircleLinkHealth\CcmBilling\Repositories\PatientServiceProcessorRepository;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Tests\CustomerTestCase;

class PatientBillingDatabaseTest extends CustomerTestCase
{
    protected PatientServiceProcessorRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repo = new PatientServiceProcessorRepository();
    }

    public function test_it_creates_ccm_status_logs_at_the_end_of_month()
    {
        $ccmStatusLogQuery = EndOfMonthCcmStatusLog::where('patient_user_id', $patientId = $this->patient()->id)
            ->where('closed_ccm_status', $ccmStatus = $this->patient()->getCcmStatus())
            ->createdOn($startOfCurrentMonth = Carbon::now()->startOfMonth(), 'chargeable_month');

        self::assertFalse($ccmStatusLogQuery->exists());

        LogPatientCcmStatusForEndOfMonth::create($patientId, $ccmStatus, $startOfCurrentMonth);

        self::assertTrue($ccmStatusLogQuery->exists());
    }

    public function test_patient_can_have_bhi_summary_that_needs_consent()
    {
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

    public function test_patient_can_have_end_of_month_ccm_status_log()
    {
        EndOfMonthCcmStatusLog::create([
            'patient_user_id'   => $this->patient()->id,
            'chargeable_month'  => $month = Carbon::now()->startOfMonth(),
            'closed_ccm_status' => $ccmStatus = Patient::WITHDRAWN_1ST_CALL,
        ]);

        self::assertTrue(
            $this->patient()
                ->endOfMonthCcmStatusLogs()
                ->createdOn($month, 'chargeable_month')
                ->where('closed_ccm_status', $ccmStatus)
                ->exists()
        );
    }

    public function test_patient_can_have_fulfilled_chargeable_monthly_summaries_attached()
    {
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
            'chargeable_service_id' => $ccmCodeId = ChargeableService::getChargeableServiceIdUsingCode($ccmCode = ChargeableService::CCM),
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

    public function test_patient_is_bhi_and_is_pcm_helpers_use_new_summaries()
    {
        $patient = $this->patient();
    
       

        //make sure patient locations have services
        $location = $patient->patientInfo->location;
        $practice = $location->practice;
        
        SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practice->id);
        AppConfig::updateOrCreate([
            'config_key'   => PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY,
            'config_value' => $practice->name,
        ]);
        
        $bhiCpmProblem = CpmProblem::withChargeableServicesForLocation($location->id)
        ->hasChargeableServiceCodeForLocation(ChargeableService::BHI, $location->id)
            ->first();
        
        dd($bhiCpmProblem->toArray());
        //create user that when processed can be BHI eligible

        //make that user BHI chargeable - core difference is the consent.

        //create user that when processed can be PCM eligible
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
                'chargeable_service_id' => $ccmCodeId = ChargeableService::getChargeableServiceIdUsingCode($ccmCode),
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

    public function test_process_single_patient_services_job_attaches_services()
    {
        //todo: change test once we have decided how to go about adding locations to new service
        $locationRepo               = new LocationProcessorEloquentRepository();
        $locationProblemServiceRepo = new LocationProblemServiceRepository();
        $patientRepo                = new PatientProcessorEloquentRepository();

        $patient = $this->patient();

        foreach ([
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::CCM_PLUS_40,
            ChargeableService::CCM_PLUS_60,
        ] as $code) {
            $locationRepo->store($locationId = $patient->getPreferredContactLocation(), $code, $startOfMonth = Carbon::now()->startOfMonth());
        }

        $cpmProblems = CpmProblem::take(5)->get()->values()->toArray();

        for ($i = 4; $i > 0; --$i) {
            $code = $i > 2 ? ChargeableService::BHI : ChargeableService::CCM;
            $locationProblemServiceRepo->store($locationId, $problemId = $cpmProblems[$i]['id'], ChargeableService::getChargeableServiceIdUsingCode($code));
            $patient->ccdProblems()->create([
                'cpm_problem_id' => $problemId,
                'name'           => str_random(8),
            ]);
        }

        self::assertFalse(ChargeablePatientMonthlySummary::where('patient_user_id', $patient->id)
            ->createdOn($startOfMonth, 'chargeable_month')
            ->exists());

        $patient = $patientRepo->patientWithBillingDataForMonth($patient->id, $startOfMonth)
            ->first();

        (new MonthlyProcessor())->process(
            (new PatientMonthlyBillingDTO())
                ->subscribe($patient->patientInfo->location->availableServiceProcessors($startOfMonth))
                ->forPatient($patient->id)
                ->forMonth($startOfMonth)
                ->withProblems(...$patient->patientProblemsForBillingProcessing()->toArray())
        );

        self::assertTrue(
            ChargeablePatientMonthlySummary::where('patient_user_id', $patient->id)
                ->createdOn($startOfMonth, 'chargeable_month')
                ->where(
                    'chargeable_service_id',
                    ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM)
                )
                ->exists()
        );
        self::assertTrue(
            ChargeablePatientMonthlySummary::where('patient_user_id', $patient->id)
                ->createdOn($startOfMonth, 'chargeable_month')
                ->where(
                    'chargeable_service_id',
                    ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI)
                )
                ->exists()
        );
    }
}
