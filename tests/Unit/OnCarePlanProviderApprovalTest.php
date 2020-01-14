<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\CarePlanModels\Entities\CarePlan;
use CircleLinkHealth\CarePlanModels\Entities\CpmProblem;
use App\Notifications\CarePlanProviderApproved;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Permission;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\CarePlanHelpers;
use Tests\TestCase;

class OnCarePlanProviderApprovalTest extends TestCase
{
    use \App\Traits\Tests\UserHelpers;
    use CarePlanHelpers;

    /**
     * @var CarePlan
     */
    protected $carePlan;

    /**
     * @var Location
     */
    protected $location;

    /**
     * @var
     */
    protected $patient;

    /**
     * @var Practice
     */
    protected $practice;

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        //Setup Practice and Location
        $this->practice = Practice::first() ?? factory(Practice::class)->create();
        $this->location = Location::firstOrCreate([
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
        $this->patient = $this->createUser($this->practice->id, 'participant');
        $this->patient->setPreferredContactLocation($this->location->id);
        $this->patient->patientInfo->save();

        $this->carePlan = $this->patient->carePlan;
    }

    public function test_care_center_can_qa_approve()
    {
        $careCenter = $this->createUser($this->practice->id, 'care-center');
        auth()->login($careCenter);

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertSee('Approve');
    }

    public function test_care_center_cannot_approve()
    {
        $careCenter = $this->createUser($this->practice->id, 'care-center');
        auth()->login($careCenter);

        $carePlan = $this->patient->carePlan;

        $carePlan->status = CarePlan::QA_APPROVED;
        $carePlan->save();

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertDontSee('Approve');
    }

    public function test_careplan_validation()
    {
        $validator = $this->carePlan->validator();

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            'The Care Plan must have two CPM problems, or one BHI problem.',
            $validator->errors()->first('conditions')
        );
        $this->assertEquals('The dob field is required.', $validator->errors()->first('dob'));
        $this->assertEquals('The mrn field is required.', $validator->errors()->first('mrn'));
        $this->assertEquals('The billing provider field is required.', $validator->errors()->first('billingProvider'));

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

        //add each one individually and check for error messages
        $this->patient->setBirthDate(Carbon::now()->subYear(20));

        $this->patient->setMRN(rand());

        $this->patient->careTeamMembers()->create([
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::BILLING_PROVIDER,
        ]);

        $this->patient->setPhone('+1-541-754-3010');

        $this->patient->save();

        $validator = $this->carePlan->validator();

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that a CarePlan is forwarded to a location.
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

        $response = $this->actingAs($this->provider)->call('POST', route('patient.careplan.approve', ['patientId' => $this->patient->id]));

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

    public function test_medical_assistant_can_approve()
    {
        $medicalAssistant = $this->createUser($this->practice->id, 'med_assistant');
        auth()->login($medicalAssistant);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertSee('Approve');
    }

    public function test_provider_can_approve()
    {
        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertSee('Approve');
    }

    public function test_provider_cannot_qa_approve()
    {
        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertDontSee('Approve');
    }

    public function test_r_n_can_approve()
    {
        $rn = $this->createUser($this->practice->id, 'registered-nurse');

        $careplanApprove = Permission::where('name', 'care-plan-approve')->first();
        $rn->attachPermission($careplanApprove->id);

        auth()->login($rn);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
            ->assertSee('Approve');
    }
}
