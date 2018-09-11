<?php

namespace Tests\unit;

use App\CarePerson;
use App\CarePlan;
use App\Location;
use App\Models\CCD\Problem;
use App\Models\CPM\CpmProblem;
use App\Notifications\CarePlanProviderApproved;
use App\Practice;
use App\User;
use Carbon\Carbon;
use Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CarePlanProviderApprovalTest extends TestCase
{
    use CarePlanHelpers,
        UserHelpers;

    /**
     * @var
     */
    protected $patient;
    protected $practice;

    /**
     * @var User $provider
     */
    protected $provider;

    /**
     * @var CarePlan
     */
    protected $carePlan;

    /**
     * @var Location
     */
    protected $location;

    public function test_careplan_validation(){
        $validator = $this->carePlan->validateCarePlan();
        $this->assertTrue($validator->fails());
        $this->assertEquals('The Care Plan must have two CPM problems, or one BHI problem.', $validator->errors()->first('conditions'));
        $this->assertEquals('The dob field is required.', $validator->errors()->first('dob'));
        $this->assertEquals('The mrn field is required.', $validator->errors()->first('mrn'));
        $this->assertEquals('The billing provider field is required.', $validator->errors()->first('billingProvider'));


        $cpmProblems = CpmProblem::get();
        $ccdProblems = $this->patient->ccdProblems()->createMany([
            ['name' => 'test' . str_random(5)],
            ['name' => 'test' . str_random(5)],
            ['name' => 'test' . str_random(5)],
        ]);
        foreach ($ccdProblems as $problem){
            $problem->cpmProblem()->associate($cpmProblems->random());
            $problem->save();
        }
        //add each one individually and check for error messages
        $this->patient->birthDate         = Carbon::now()->subYear(20);
        $this->patient->MRN               = rand();
        $this->patient->careTeamMembers()->create([
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::BILLING_PROVIDER,
        ]);
        $this->patient->phone             = '111-234-5678';
        $this->patient->save();


        $validator = $this->carePlan->validateCarePlan();
        $this->assertTrue($validator->passes());

    }

    public function test_provider_cannot_qa_approve()
    {
        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertDontSee('Approve/Next');
    }

    public function test_provider_can_approve()
    {
        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertSee('Approve/Next');
    }

    public function test_medical_assistant_can_approve()
    {
        $medicalAssistant = $this->createUser($this->practice->id, 'med_assistant');
        auth()->login($medicalAssistant);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertSee('Approve/Next');
    }

    public function test_r_n_can_approve()
    {
        Practice::find($this->practice->id)->cpmSettings()->update([
            'rn_can_approve_careplans' => true,
        ]);
        $rn = $this->createUser($this->practice->id, 'registered-nurse');
        auth()->login($rn);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertSee('Approve/Next');
    }

    public function test_care_center_cannot_approve()
    {
        $careCenter = $this->createUser($this->practice->id, 'care-center');
        auth()->login($careCenter);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertDontSee('Approve/Next');
    }

    public function test_care_center_can_qa_approve()
    {
        $careCenter = $this->createUser($this->practice->id, 'care-center');
        auth()->login($careCenter);

        $response = $this->get(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
                         ->assertSee('Approve/Next');
    }

    /**
     * Test that a CarePlan is forwarded to a location
     */
    public function test_it_forwards_careplan_to_location()
    {
        Notification::fake();

        $this->carePlan->forward();

        Notification::assertSentTo(
            $this->patient->patientInfo->location,
            CarePlanProviderApproved::class
        );
    }

    public function test_it_forwards_careplan_when_provider_approved()
    {
        $this->carePlan->status = CarePlan::QA_APPROVED;
        $this->carePlan->save();

        $response = $this->call('GET', route('patient.careplan.approve', ['patientId' => $this->patient->id]));

        $response->assertStatus(302);
        $response->assertRedirect(route('patient.careplan.print', [
            'patientId'    => $this->patient->id,
            'clearSession' => false,
        ]));

        $this->carePlan = $this->carePlan->fresh();

        $this->assertEquals(1, $this->carePlan->notifications()->count());

        $this->assertEquals($this->carePlan->status, CarePlan::PROVIDER_APPROVED);
        $this->assertEquals($this->carePlan->provider_approver_id, $this->provider->id);
        $this->assertTrue(Carbon::now()->isSameDay($this->carePlan->provider_date));
    }

    protected function setUp()
    {
        parent::setUp();

        //Setup Practice and Location
        $this->practice = factory(Practice::class)->create();
        $this->location = factory(Location::class)->create([
            'practice_id' => $this->practice->id,
        ]);

        //Setup Practice Settings
        $settings                    = $this->practice->cpmSettings();
        $settings->efax_pdf_careplan = true;
        $settings->dm_pdf_careplan   = true;
        $settings->save();

        //Setup Provider
        $this->provider = $this->createUser($this->practice->id);
        auth()->login($this->provider);

        //Setup Patient and CarePlan
        $this->patient                                          = $this->createUser($this->practice->id, 'participant');
        $this->patient->patientInfo->preferred_contact_location = $this->location->id;
        $this->patient->patientInfo->save();

        $this->carePlan = $this->patient->carePlan;
    }
}
