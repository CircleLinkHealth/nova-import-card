<?php

namespace Tests\unit;

use App\Practice;
use Tests\TestCase;
use App\CarePlan;
use App\User;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

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

    protected function setUp()
    {
        parent::setUp();
        $this->practice = factory(Practice::class)->create();
        $this->provider = $this->createUser($this->practice->id);
        auth()->login($this->provider);
        $this->patient = $this->createUser($this->practice->id, 'participant');
    }
}
