<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Algorithms\Calls\NurseFinder\NurseFinderEloquentRepository;
use App\Call;
use App\Note;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Services\CCD\CcdProblemService;
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
use Illuminate\Support\Facades\DB;
use Tests\Helpers\CarePlanHelpers;
use Tests\TestCase;

class PatientAttestedConditionsTest extends TestCase
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
    public function test_api_fetches_unique_conditions_for_nurse_attestation_modal()
    {
        $patientProblems = $this->patient->ccdProblems()->get();

        $problem = $patientProblems->first();

        $ccdProblem = [
            'name'           => 'another random ccd problem',
            'userId'         => $this->patient->id,
            'is_monitored'   => 1,
            'cpm_problem_id' => null,
            //add already attached problem icd10code to create duplicate
            'icd10' => $problem->icd10Code(),
        ];

        (app(CcdProblemService::class))->addPatientCcdProblem($ccdProblem);

        $responseData = $this->actingAs($this->nurse)->call('GET', url("/api/patients/{$this->patient->id}/problems/unique-to-attest"))
            ->assertOk()
            ->getOriginalContent();

        //assert that duplicate will not be fetched
        $this->assertNotEquals($responseData->count(), $this->patient->ccdProblems()->count());
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

        //check timestamps exist
        $this->assertNotNull($call->attestedProblems()->first()->created_at);

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
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        $lastMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth());

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);
        $this->assertTrue($responseData['attestationRequirements']['has_ccm']);
    }

    public function test_attestation_validation_for_complex_ccm_and_bhi_code()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::BHI,
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        $lastMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth());

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);
        $this->assertTrue(0 === $responseData['attestationRequirements']['bhi_problems_attested']);
        $this->assertTrue($responseData['attestationRequirements']['has_ccm']);
        $this->assertTrue(0 === $responseData['attestationRequirements']['ccm_problems_attested']);
    }

    public function test_attestation_validation_for_complex_ccm_and_bhi_code_is_not_applicable_if_conditions_already_attested()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::BHI,
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        //this should work even with unfulfilled services
        $pms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth(), true);

        //attest 2 ccm and 1 bhi condition
        $problems = $this->patient->ccdProblems()->with(['cpmProblem'])->get();

        $attestedProblems = $problems->where('cpmProblem.is_behavioral', true)
            ->take(1)
            ->merge(
                $problems->where('cpmProblem.is_behavioral', false)->take(2)
            )
            ->pluck('id')
            ->toArray();

        $pms->syncAttestedProblems($attestedProblems);

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);
        $this->assertTrue(1 === $responseData['attestationRequirements']['bhi_problems_attested']);
        $this->assertTrue(2 === $responseData['attestationRequirements']['ccm_problems_attested']);
    }

    public function test_command_to_attest_problems_to_addendum()
    {
        $this->actingAs($this->nurse);

        self::assertNotNull(auth()->user(), 'Logged in user needed for this test case');

        $call = $this->patient->inboundCalls()->with(['note'])->first();
        $note = $call->note;
        $note->addendums()->create(
            [
                'body'           => 'test addendum body',
                'author_user_id' => $this->nurse->id,
            ]
        );
        $addendum        = $note->addendums()->first();
        $pms             = $this->patient->patientSummaryForMonth();
        $patientProblems = $this->patient->ccdProblems()->get();

        self::assertNotNull($pms);
        self::assertEquals($call->attestedProblems()->count(), 0);

        Artisan::call('billing:attest-problems', [
            'problemIds' => $patientProblems->pluck('id')->implode(','),
            'noteId'     => $note->id,
            'addendumId' => $addendum->id,
        ]);

        $callAttestedProblems = $call->attestedProblems()->get();

        self::assertEquals($callAttestedProblems->count(), $patientProblems->count());
        self::assertNotNull($callAttestedProblems->first()->created_at);
        self::assertEquals($pms->attestedProblems()->count(), $patientProblems->count());

        self::assertTrue(DB::table('call_problems')->where('addendum_id', $addendum->id)->count() === $patientProblems->count());
    }

    public function test_complex_validation_rules_disabled_for_practice()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', '');

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertTrue($responseData['attestationRequirements']['disabled']);
    }

    public function test_patient_summaries_are_created_with_any_previous_months_services_from_notes_create_route()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
            ChargeableService::BHI,
            ChargeableService::GENERAL_CARE_MANAGEMENT,
        ])->pluck('id')->toArray();

        $previousMonthsPms = $this->setupPms($charggeableServiceIds, Carbon::now()->startOfMonth()->subMonth(3));

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);

        $currentPms = PatientMonthlySummary::where('patient_id', $this->patient->id)
            ->where('month_year', Carbon::now()->startOfMonth())
            ->first();

        $this->assertNotNull($currentPms);

        //they should not be brought because the have is_fulfilled 0
        $this->assertEquals(0, $currentPms->chargeableServices->count());

        //un-fulfilled will be brought only in new relationship
        $currentChargeableServices = $currentPms->allChargeableServices;

        $this->assertEquals(collect($charggeableServiceIds)->count(), $currentChargeableServices->count());

        foreach ($charggeableServiceIds as $id) {
            $this->assertTrue(1 == $currentChargeableServices->where('id', $id)->count());
        }
    }

    public function test_patient_summaries_are_created_with_last_months_services_from_notes_create_route()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

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

        //they should not be brought because the have is_fulfilled 0
        $this->assertEquals(0, $currentPms->chargeableServices->count());

        //un-fulfilled will be brought only in new relationship
        $currentChargeableServices = $currentPms->allChargeableServices;

        $this->assertEquals(collect($charggeableServiceIds)->count(), $currentChargeableServices->count());

        foreach ($charggeableServiceIds as $id) {
            $this->assertTrue(1 == $currentChargeableServices->where('id', $id)->count());
        }
    }

    public function test_patient_summaries_have_services_according_to_practice_and_patient_problems_if_not_last_month_pms()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        //patient already has more than 2 ccm problems on setup, attach only CCM to practice
        $this->practice->chargeableServices()->sync($charggeableServiceIds);

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);

        $currentPms = PatientMonthlySummary::where('patient_id', $this->patient->id)
            ->where('month_year', Carbon::now()->startOfMonth())
            ->first();

        $this->assertNotNull($currentPms);

        //they should not be brought because the have is_fulfilled 0
        $this->assertEquals(0, $currentPms->chargeableServices->count());

        //un-fulfilled will be brought only in new relationship
        $currentChargeableServices = $currentPms->allChargeableServices;

        $this->assertEquals(collect($charggeableServiceIds)->count(), $currentChargeableServices->count());

        foreach ($charggeableServiceIds as $id) {
            $this->assertTrue(1 == $currentChargeableServices->where('id', $id)->count());
        }
    }

    /**
     * This is to test passing null on PMS::attachLastMonthsChargeableServicesIfYouShould.
     *
     * In Notes controller, if PMS for current month exists and no services attach
     * It will call the method without passing a previous month summary.
     */
    public function test_patient_summaries_have_services_according_to_practice_and_patient_problems_if_not_last_month_pms_and_existing_pms_no_services()
    {
        AppConfig::set('complex_attestation_requirements_for_practice', $this->practice->id);

        $charggeableServiceIds = ChargeableService::whereIn('code', [
            ChargeableService::CCM,
        ])->pluck('id')->toArray();

        //patient already has more than 2 ccm problems on setup, attach only CCM to practice
        $this->practice->chargeableServices()->sync($charggeableServiceIds);

        //create current month summary with no codes
        $this->setupPms([]);

        $responseData = $this->actingAs($this->nurse)->call('GET', route('patient.note.create', ['patientId' => $this->patient->id]))
            ->assertOk()
            ->getOriginalContent()->getData();

        $this->assertFalse($responseData['attestationRequirements']['disabled']);

        $currentPms = PatientMonthlySummary::where('patient_id', $this->patient->id)
            ->where('month_year', Carbon::now()->startOfMonth())
            ->first();

        $this->assertNotNull($currentPms);

        //they should not be brought because the have is_fulfilled 0
        $this->assertEquals(0, $currentPms->chargeableServices->count());

        //un-fulfilled will be brought only in new relationship
        $currentChargeableServices = $currentPms->allChargeableServices;

        $this->assertEquals(collect($charggeableServiceIds)->count(), $currentChargeableServices->count());

        foreach ($charggeableServiceIds as $id) {
            $this->assertTrue(1 == $currentChargeableServices->where('id', $id)->count());
        }
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_bhi()
    {
        $bhiCsId       = ChargeableService::bhi()->first()->id;
        $pms           = $this->setupPms([$bhiCsId], now()->subMonth());
        $pms->bhi_time = 1440;
        $pms->save();

        $this->setupPracticeServices([$bhiCsId]);

        $this->assertTrue($pms->hasServiceCode(ChargeableService::BHI));
        $this->assertEquals($pms->bhiAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign(now()->subMonth());

        $pms->load('attestedProblems');

        $this->assertTrue(1 == $pms->bhiAttestedProblems()->count());
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_ccm()
    {
        $pms           = $this->setupPms([ChargeableService::ccm()->first()->id], now()->subMonth());
        $pms->ccm_time = 1440;
        $pms->save();

        $this->assertTrue($pms->hasServiceCode(ChargeableService::CCM));
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign(now()->subMonth());

        $pms->load('attestedProblems');

        $this->assertTrue(4 == $pms->ccmAttestedProblems()->count());
    }

    public function test_problems_are_automatically_attested_to_pms_if_they_should_pcm()
    {
        $pcmCsId = ChargeableService::pcm()->first()->id;

        $pms           = $this->setupPms([$pcmCsId], now()->subMonth());
        $pms->ccm_time = 1440;
        $pms->save();

        $this->setupPracticeServices([$pcmCsId]);

        $this->assertTrue($pms->hasServiceCode(ChargeableService::PCM));
        $this->assertEquals($pms->ccmAttestedProblems()->count(), 0);

        $this->runCommandToAutoAssign(now()->subMonth());

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

    private function runCommandToAutoAssign(Carbon $month = null)
    {
        $month = $month ? $month : Carbon::now();
        Artisan::call(
            'generate:abp',
            [
                '--reset-actor' => true,
                '--auto-attest' => true,
                'date'          => $month->startOfMonth()->toDateString(),
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
                'is_monitored'   => 1,
                'cpm_problem_id' => $problem->id,
            ]);
        }

        app(NurseFinderEloquentRepository::class)->assign($this->patient->id, $this->nurse->id);

        $note = $this->patient->notes()->create([
            'author_id'                => $this->nurse->id,
            'body'                     => 'test',
            'type'                     => 'General (Clinical)',
            'performed_at'             => Carbon::now(),
            'logger_id'                => $this->nurse->id,
            'status'                   => Note::STATUS_COMPLETE,
            'successful_clinical_call' => 1,
        ]);

        //setup call
        Call::create([
            'service' => 'phone',
            'status'  => 'scheduled',
            'note_id' => $note->id,

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

    private function setupPms(array $chargeableServiceIds, Carbon $month = null, $attachAsUnfulfilled = false)
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

        $toSync = $chargeableServiceIds;

        if ($attachAsUnfulfilled) {
            $toSync = [];
            foreach ($chargeableServiceIds as $id) {
                $toSync[$id] = [
                    'is_fulfilled' => false,
                ];
            }
        }

        $pms->chargeableServices()->sync($toSync);

        return $pms;
    }

    private function setupPracticeServices(array $chargeableServiceIds)
    {
        $this->practice->chargeableServices()->sync($chargeableServiceIds);
    }
}
