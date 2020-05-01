<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\ExternalApi;

use App\Services\AthenaAPI\Calls;
use App\ValueObjects\Athena\Patient;
use App\ValueObjects\Athena\Problem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AthenaApiCallsTest extends TestCase
{
    use WithFaker;

    /**
     * @var Calls
     */
    private $api;
    private $athenaDepartmentId;
    private $athenaPatientId;
    private $athenaPracticeId;
    private $fakePatient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = app(\CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation::class);

        $this->athenaPracticeId   = 195900;
        $this->athenaDepartmentId = 1;
        $this->fakePatient        = $this->fakePatient();
        $this->athenaPatientId    = $this->createAthenaApiPatient($this->fakePatient);
    }

    public function test_it_creates_and_gets_patient_appointments()
    {
        //creates slot and appointment
        $appointment = $this->createNewAthenaAppointment();

        //retrieves all patient appointments
        $patientAppointments = $this->api->getPatientAppointments($this->athenaPracticeId, $this->athenaPatientId);

        $this->assertTrue(is_array($patientAppointments));

        //test appointment notes
        $note = $this->addAppointmentNote($appointment[0]['appointmentid']);

        $this->assertTrue(is_array($note));

        $appointmentNotes = $this->api->getAppointmentNotes($this->athenaPracticeId, $appointment[0]['appointmentid']);

        $this->assertTrue(is_array($appointmentNotes));
    }

    public function test_it_gets_problems()
    {
        $problem1 = $this->addProblem($this->fakeProblem(234347009, $this->athenaPatientId, $this->athenaPracticeId));
        $problem2 = $this->addProblem($this->fakeProblem(195949008, $this->athenaPatientId, $this->athenaPracticeId));

        $response = $this->api->getPatientProblems(
            $this->athenaPatientId,
            $this->athenaPracticeId,
            $this->athenaDepartmentId
        );

        $this->assertTrue(is_array($response));
        $this->assertEquals(2, $response['totalcount']);
        $this->assertArrayHasKey('problems', $response);

        foreach ($response['problems'] as $problem) {
            $this->assertTrue(in_array($problem['problemid'], [$problem1, $problem2]));
        }
    }

    public function test_it_gets_zero_problems()
    {
        //make a call to ad
        $response = $this->api->getPatientProblems(
            $this->athenaPatientId,
            $this->athenaPracticeId,
            $this->athenaDepartmentId
        );

        $this->assertTrue(is_array($response));
        $this->assertEquals([], $response['problems']);
        $this->assertEquals(0, $response['totalcount']);
    }

    private function addAppointmentNote($appointmentId)
    {
        $noteText = 'TEST';

        $response = $this->api->postAppointmentNotes($this->athenaPracticeId, $appointmentId, $noteText);

        if (array_key_exists('success', $response)) {
            $this->assertTrue(true);

            return $response;
        }

        $this->assertTrue(false);
    }

    private function addProblem(Problem $problem)
    {
        $problem = $this->api->addProblem($problem);

        if (array_key_exists('errormessage', $problem)) {
            $this->assertTrue(false, $problem['errormessage']);

            return false;
        }

        $this->assertTrue(true, 'Problem created in AthenaAPI');

        return $problem['problemid'];
    }

    private function createAppointmentSlot()
    {
        $providerId      = '86';
        $reasonId        = '962';
        $appointmentDate = Carbon::now()->addMonth()->toDateString();
        $appointmentTime = '11:00';

        $response = $this->api->createNewAppointmentSlot(
            $this->athenaPracticeId,
            $this->athenaDepartmentId,
            $providerId,
            $reasonId,
            $appointmentDate,
            $appointmentTime
        );

        $appointmentId = '';

        foreach ($response as $id => $appointments) {
            foreach ($appointments as $appointment => $time) {
                $appointmentId = $appointment;
            }
        }

        return $appointmentId;
    }

    private function createAthenaApiPatient()
    {
        $patients = $this->api->createNewPatient($this->fakePatient);

        $this->assertTrue(is_array($patients));

        if (array_key_exists(0, $patients)) {
            $this->assertTrue(true);

            return $patients[0]['patientid'];
        }

        $this->assertTrue(false);
    }

    private function createNewAthenaAppointment()
    {
        $providerId = '86';
        $reasonId   = '962';

        $appointmentId = $this->createAppointmentSlot();

        $response = $this->api->createNewAppointment(
            $this->athenaPracticeId,
            $this->athenaDepartmentId,
            $this->athenaPatientId,
            $providerId,
            $appointmentId,
            $reasonId
        );

        if (array_key_exists(0, $response)) {
            $this->assertTrue(true);

            return $response;
        }

        $this->assertTrue(false);
    }

    private function fakePatient()
    {
        $patient = new Patient();
        $patient->setPracticeId(195900);
        $patient->setDepartmentId(1);
        $patient->setFirstName($this->faker()->firstName());
        $patient->setLastName($this->faker()->lastName);
        $patient->setDob(Carbon::now()->subYear(50));
        $patient->setAddress1($this->faker()->streetAddress);
        $patient->setAddress2('Apt 123');
        $patient->setCity($this->faker()->city);
        $patient->setState($this->faker()->randomElement(['NY', 'NJ', 'AR', 'CA']));
        $patient->setZip('07601');
        $patient->setGender($this->faker()->randomElement(['F', 'M']));
        $patient->setHomePhone('2014445555');
        $patient->setMobilePhone('2012223333');

        return $patient;
    }

    private function fakeProblem($snomedCode, $athenaPatientId, $athenaPracticeId)
    {
        $problem = new Problem();
        $problem->setDepartmentId(1);
        $problem->setPracticeId($athenaPracticeId);
        $problem->setPatientId($athenaPatientId);
        $problem->setSnomedCode($snomedCode);
        $problem->setStatus('CHRONIC');

        return $problem;
    }
}
