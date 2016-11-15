<?php

use App\User;
use Modelizer\Selenium\SeleniumTestCase;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

class TimeTrackingTest extends SeleniumTestCase
{
    use CarePlanHelpers,
        UserHelpers;

    protected $patient;
    protected $provider;

    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->createUser();

        $this->patient = User::ofType('participant')
            ->with('patientInfo')
            ->intersectPracticesWith($this->provider)
            ->first();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPopupShowsUpAfterIdle()
    {
        $initialCcmTime = $this->patient->ccmTime;

        $this->actingAs($this->provider)
            ->visit(route('patient.summary', [
                'patientId' => $this->patient->id,
            ]))
            ->click('Edit Care Plan')
            ->wait(10)
            ->click('Patient Overview')
            ->wait(7)
            ->click('View Care Plan')
            ->wait(10)
            ->click('Notes/Offline Activity')
            ->click('Notes/Offline Activities')
            ->wait(3)
            ->click('Patient Overview');

        $this->assertGreaterThan($initialCcmTime, $this->patient->ccmTime);

        $newCcmTime = $this->patient->ccmTime - $initialCcmTime;
        $this->assertTrue(($newCcmTime > 25) && ($newCcmTime < 35));

        echo "\n\nCCM Time Recorded: $newCcmTime\n\n";
    }
}
