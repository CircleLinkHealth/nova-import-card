<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Http\Controllers\NotesController;
use App\Services\Calls\SchedulerService;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Faker\Factory;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\MakesSafeRequests;
use Tests\TestCase;

class PatientWithdrawnTest extends TestCase
{
    use CarePlanHelpers;
    use MakesSafeRequests;
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
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
    public function test_withdrawn_1st_call_from_notes_page()
    {
        $this->actingAs($this->nurse);

        $this->assertTrue(Patient::ENROLLED == $this->patient->getCcmStatus());
        $this->assertTrue($this->patient->onFirstCall());

        $this->makeCallToNotesController(Patient::WITHDRAWN);

        $this->assertTrue(Patient::WITHDRAWN_1ST_CALL == $this->patient->getCcmStatus());
    }

    public function test_withdrawn_from_all_users_page()
    {
    }

    /**
     * A basic unit test example.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @return void
     */
    public function test_withdrawn_from_notes_page()
    {
        $this->actingAs($this->nurse);

        $this->createPatientCall('reached');

        $this->assertTrue(Patient::ENROLLED == $this->patient->getCcmStatus());
        $this->assertTrue( ! $this->patient->onFirstCall());

        $this->makeCallToNotesController(Patient::WITHDRAWN);

        $this->assertTrue(Patient::WITHDRAWN == $this->patient->getCcmStatus());
    }

    public function test_withdrawn_from_patient_profile_page()
    {
        $this->assertTrue(true);
    }

    private function createPatientCall($status = 'scheduled')
    {
        Call::create([
            'service' => 'phone',
            'status'  => $status,

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

    /**
     * Meant to be needed to call NotesController->store
     * Currently failing because of SafeRequest
     * Todo: make tests able to call controller actions expecting SafeRequests.
     *
     * @param  mixed $ccmStatus
     * @return array
     */
    private function getStoreCallInput($ccmStatus)
    {
        return [
            'ccm_status'             => $ccmStatus,
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
        ];
    }

    private function makeCallToNotesController($status)
    {
        //still not able to make a request to a controller that uses Safe Request with $this->call
        //This is a workaround
        $request = $this->safeRequest(
            route('patient.note.store', ['patientId' => $this->patient->id]),
            'POST',
            $this->getStoreCallInput($status)
        );

        $controller = app(NotesController::class);

        $controller->store($request, app(SchedulerService::class), $this->patient->id);

        $this->patient->load('patientInfo');
    }

    private function setupPatient()
    {
        $this->patient = $this->createUser($this->practice->id, 'participant');
        $this->patient->setPreferredContactLocation($this->location->id);
        $this->patient->patientInfo->save();

        [$bhi, $ccm] = CpmProblem::get()->partition(function ($p) {
            return $p->is_behavioral;
        });

        $problemsForPatient = $bhi->take(2)->merge($ccm->take(8));

        foreach ($problemsForPatient as $bhiProblem) {
            $this->patient->ccdProblems()->create([
                'name'           => $bhiProblem->name,
                'cpm_problem_id' => $bhiProblem->id,
            ]);
        }

        //setup call
        $this->createPatientCall();
    }
}
