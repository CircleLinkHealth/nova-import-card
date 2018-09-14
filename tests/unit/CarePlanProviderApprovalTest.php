<?php

namespace Tests\unit;

use App\CarePlan;
use App\Location;
use App\Notifications\CarePlanProviderApproved;
use App\Permission;
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

    public function test_provider_cannot_qa_approve()
    {
        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
                         ->assertDontSee('Approve');
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

    public function test_care_center_cannot_approve()
    {
        $careCenter = $this->createUser($this->practice->id, 'care-center');
        auth()->login($careCenter);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $response = $this->get(route('patient.careplan.print', [
            'patientId' => $this->patient->id,
        ]))
                         ->assertDontSee('Approve');
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
