<?php

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

    /**
     * @var User $provider
     */
    protected $provider;

    public function test_provider_can_approve()
    {
        $this->visit(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
            ->see('Approve/Next');
    }

    public function test_medical_assistant_can_approve()
    {
        $medicalAssistant = $this->createUser(8, 'med_assistant');
        auth()->login($medicalAssistant);

        $this->visit(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
            ->see('Approve/Next');
    }

    public function test_r_n_can_approve()
    {
        $rn = $this->createUser(8, 'registered-nurse');
        auth()->login($rn);

        $this->visit(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
            ->see('Approve/Next');
    }

    public function test_care_center_cannot_approve()
    {
        $careCenter = $this->createUser(8, 'care-center');
        auth()->login($careCenter);

        $this->patient->carePlan->status = CarePlan::QA_APPROVED;
        $this->patient->carePlan->save();

        $this->visit(route('patient.careplan.show', [
            'patientId' => $this->patient->id,
            'page'      => 3,
        ]))
            ->dontSee('Approve/Next');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->provider = $this->createUser(8);
        auth()->login($this->provider);
        $this->patient = $this->createUser(8, 'participant');
    }
}
