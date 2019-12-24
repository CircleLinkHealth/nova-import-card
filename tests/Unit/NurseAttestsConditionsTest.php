<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Models\CPM\CpmProblem;
use App\Traits\Tests\UserHelpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use Faker\Factory;
use Tests\DuskTestCase;
use Tests\Helpers\CarePlanHelpers;

class NurseAttestsConditionsTest extends DuskTestCase
{
    use CarePlanHelpers;
    use UserHelpers;

    protected $faker;
    protected $location;
    protected $nurse;
    protected $patient;

    protected $practice;
    protected $provider;

    protected function setUp()
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
        auth()->login($this->nurse);

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

    public function test_modal_pops_up()
    {
        //todo: fix dusk
//        $this->browse(function ($browser) {
//            $browser->loginAs($this->nurse)
//                    ->visit(route('patient.note.create', [
//                        'patientId' => $this->patient->id,
//                    ]))
//                    ->select('type', 'Review Patient Progress')
//                    ->value('call_status', 'reached')
//                    ->value('phone', 'outbound')
//                    ->press('Save Note')
//                    ->assertSee('Please select all conditions addressed in this call');
//        });
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

    private function setupPatient()
    {
        $this->patient = $this->createUser($this->practice->id, 'participant');
        $this->patient->setPreferredContactLocation($this->location->id);
        $this->patient->patientInfo->save();

        $cpmProblems = CpmProblem::get();
        $ccdProblems = $this->patient->ccdProblems()->createMany([
            ['name' => 'test'.str_random(5)],
            ['name' => 'test'.str_random(5)],
            ['name' => 'test'.str_random(5)],
        ]);

        foreach ($ccdProblems as $problem) {
            $problem->cpmProblem()->associate($cpmProblems->random());
            $problem->save();
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
}
