<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Faker\Factory;
use Illuminate\Support\Facades\Artisan;
use Tests\DuskTestCase;
use Tests\Helpers\CarePlanHelpers;

class PatientAttestedConditionsTest extends DuskTestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    /**
     * @var Factory
     */
    protected $faker;
    /**
     * @var Location
     */
    protected $location;
    /**
     * @var User
     */
    protected $nurse;
    /**
     * @var User
     */
    protected $patient;

    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var User
     */
    protected $provider;

    /**
     * @var PatientSummaryEloquentRepository
     */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->practice = Practice::first() ?? factory(Practice::class)->create();
        $this->location = Location::firstOrCreate([
            'practice_id' => $this->practice->id,
        ]);

        $this->provider = $this->createUser($this->practice->id);
        $this->nurse    = $this->createUser($this->practice->id, 'care-center');

        $this->setupPatient();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_asserted_problems_are_attached_to_scheduled_call()
    {
        $this->actingAs($this->nurse);

        $call            = $this->patient->inboundCalls()->first();
        $pms             = $this->patient->patientSummaryForMonth();
        $patientProblems = $this->patient->ccdProblems()->get();

        $this->assertNotNull($pms);
        $this->assertEquals($call->attestedProblems()->count(), 0);

        //attach problems to call
        $call->attachAttestedProblems($patientProblems->pluck('id')->toArray());

        //check call
        $this->assertEquals($call->attestedProblems()->count(), $patientProblems->count());

        //assert that asserted attached to calls exist on the summary
        $this->assertEquals($pms->attestedProblems()->count(), $patientProblems->count());
    }

    public function test_attest_bhi_problems_exist_in_ccm_column_if_practice_does_not_have_bhi_enabled()
    {
        $ccmCsId       = ChargeableService::ccm()->first()->id;
        $pms           = $this->setupPms([$ccmCsId]);
        $pms->ccm_time = 1440;
        $pms->save();

        $this->setupPracticeServices([
            $ccmCsId,
        ]);

        $this->assertTrue($pms->hasServiceCode(ChargeableService::CCM));
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);

        $pms->syncAttestedProblems($this->patient->ccdProblems->pluck('id')->toArray());

        $pms->load('attestedProblems');

        $this->assertTrue(10 == $pms->ccmAttestedProblems()->count());
    }

    public function test_attestation_validation_for_ccm_code()
    {
        AppConfig::create([
            'config_key'   => 'complex_attestation_requirements_for_practice',
            'config_value' => $this->practice->id,
        ]);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        $lastMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth());

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);
        $this->assertTrue($responseData['attestationRequirements']['ccm_2']);
    }

    public function test_attestation_validation_for_complex_ccm_and_bhi_code()
    {
        AppConfig::create([
            'config_key'   => 'complex_attestation_requirements_for_practice',
            'config_value' => $this->practice->id,
        ]);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::BHI,
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        $lastMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth());

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);
        $this->assertTrue($responseData['attestationRequirements']['bhi_1']);
        $this->assertTrue($responseData['attestationRequirements']['ccm_2']);
        $this->assertTrue($responseData['attestationRequirements']['is_complex']);
    }

    public function test_complex_validation_rules_disabled_for_practice()
    {
        AppConfig::create([
            'config_key'   => 'complex_attestation_requirements_for_practice',
            'config_value' => '',
        ]);

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertTrue($responseData['attestationRequirements']['disabled']);
    }

    public function test_patient_summaries_are_created_with_last_months_services_from_notes_create_route()
    {
        AppConfig::create([
            'config_key'   => 'complex_attestation_requirements_for_practice',
            'config_value' => $this->practice->id,
        ]);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ])->pluck('id')->toArray();

        $lastMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth());

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);

        $currentPms = PatientMonthlySummary::where('patient_id', $this->patient->id)
            ->where('month_year', Carbon::now()->startOfMonth())
            ->first();

        $this->assertNotNull($currentPms);

        $currentChargeableServices = $currentPms->chargeableServices;

        $this->assertEquals(collect($charggeableServiceIds)->count(), $currentChargeableServices->count());

        foreach ($charggeableServiceIds as $id) {
            $this->assertNotNull($currentChargeableServices->where('id', $id));
        }
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_bhi()
    {
        $bhiCsId       = ChargeableService::bhi()->first()->id;
        $pms           = $this->setupPms([$bhiCsId]);
        $pms->bhi_time = 1440;
        $pms->save();

        $this->setupPracticeServices([$bhiCsId]);

        $this->assertTrue($pms->hasServiceCode(ChargeableService::BHI));
        $this->assertEquals($pms->bhiAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign();

        $pms->load('attestedProblems');

        $this->assertTrue(1 == $pms->bhiAttestedProblems()->count());
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_ccm()
    {
        $pms           = $this->setupPms([ChargeableService::ccm()->first()->id]);
        $pms->ccm_time = 1440;
        $pms->save();

        $this->assertTrue($pms->hasServiceCode(ChargeableService::CCM));
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign();

        $pms->load('attestedProblems');

        $this->assertTrue(4 == $pms->ccmAttestedProblems()->count());
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_pcm()
    {
        $pcmCsId = ChargeableService::pcm()->first()->id;

        $pms           = $this->setupPms([$pcmCsId]);
        $pms->ccm_time = 1440;
        $pms->save();

        $this->setupPracticeServices([$pcmCsId]);

        $this->assertTrue($pms->hasServiceCode(ChargeableService::PCM));
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign();

        $pms->load('attestedProblems');

        $this->assertTrue(4 == $pms->ccmAttestedProblems()->count());
    }

    /**
     * Meant to be needed to call NotesController->store
     * Currently failing because of SafeRequest
     * Todo: make tests able to call controller actions expecting SafeRequests.
     *
     * @return array
     */
    private function getStoreCallInput()
    {
        return [
            'ccm_status'             => 'enrolled',
            'withdrawn_reason'       => 'No Longer Interested',
            'withdrawn_reason_other' => '',
            'general_comment'        => '',
            'type'                   => 'Review Care Plan',
            'performed_at'           => '2019-12-17T12:26',
            'phone'                  => 'outbound',
            'call_status'            => 'reached',
            'tcm'                    => 'hospital',
            'summary'                => '',
            'body'                   => 'test',
            'patient_id'             => "{$this->patient->id}",
            'logger_id'              => "{$this->nurse->id}",
            'author_id'              => "{$this->nurse->id}",
            'programId'              => "{$this->practice->id}",
            'task_status'            => '',
            'attested_problems'      => $this->patient->ccdProblems()->pluck('id')->toArray(),
        ];
    }

    private function runCommandToAutoAssign()
    {
        Artisan::call(
            'generate:abp',
            [
                '--reset-actor' => true,
                '--auto-attest' => true,
                'date'          => Carbon::now()->startOfMonth()->toDateString(),
                'practiceIds'   => "{$this->patient->program_id}",
            ]
        );
    }

    private function setupPatient()
    {
        $this->patient = $this->createUser($this->practice->id, 'participant');
        $this->patient->setPreferredContactLocation($this->location->id);
        $this->patient->patientInfo->save();
        $this->repo = app(PatientSummaryEloquentRepository::class);

        [$bhi, $ccm] = CpmProblem::get()->partition(function ($p) {
            return $p->is_behavioral;
        });

        $problemsForPatient = $bhi->take(2)->merge($ccm->take(8));

        foreach ($problemsForPatient as $problem) {
            $this->patient->ccdProblems()->create([
                'name'           => $problem->name,
                'cpm_problem_id' => $problem->id,
            ]);
        }

        //setup call
        Call::create([
            'service' => 'phone',
            'status'  => 'scheduled',

            'scheduler' => 'core algorithm',

            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,

            'inbound_phone_number'  => $this->faker->phoneNumber,
            'outbound_phone_number' => $this->faker->phoneNumber,
            'scheduled_date'        => Carbon::now()->toDateString(),

            'inbound_cpm_id'  => $this->patient->id,
            'outbound_cpm_id' => $this->nurse->id,

            'is_cpm_outbound' => true,
        ]);
    }

    private function setupPms(array $chargeableServiceIds, Carbon $month = null)
    {
        if ( ! $month) {
            $month = Carbon::now();
        }

        $pms = PatientMonthlySummary::updateOrCreate([
            'patient_id' => $this->patient->id,
            'month_year' => $month->startOfMonth()->toDateString(),
        ], [
            'total_time'             => 3000,
            'no_of_successful_calls' => 1,
        ]);

        $pms->chargeableServices()->sync($chargeableServiceIds);

        return $pms;
    }

    private function setupPracticeServices(array $chargeableServiceIds)
    {
        $this->practice->chargeableServices()->sync($chargeableServiceIds);
    }
}
