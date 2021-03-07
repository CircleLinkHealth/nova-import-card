<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\PatientAwvSurveyInstanceStatusView;
use Tests\Helpers\PatientHelpers;
use Tests\Helpers\UserHelpers;

class AddAppointmentToPatientTest extends \CircleLinkHealth\Core\Tests\TestCase
{
    use PatientHelpers;
    use UserHelpers;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_add_appointment_to_patient()
    {
        $this->be($this->createAdminUser());
        $patient = $this->createPatient();

        $newer = now();
        $patient->addAppointment($newer);
        $older = now()->subDay(1);
        $patient->addAppointment($older);

        $this->assertEquals(2, $patient->awvAppointments()->count());

        $this->assertEquals($newer->toDateTimeString(), $patient->latestAwvAppointment()->appointment->toDateTimeString());

        /** @var PatientAwvSurveyInstanceStatusView $awvStatusRecord */
        $awvStatusRecord = PatientAwvSurveyInstanceStatusView::where('patient_id', '=', $patient->id)->first();
        $this->assertNotNull($awvStatusRecord->appointment);
        $this->assertEquals($newer, $awvStatusRecord->appointment);
    }
}
