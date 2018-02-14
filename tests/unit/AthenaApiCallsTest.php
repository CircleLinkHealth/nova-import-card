<?php

namespace Tests\Unit;

use App\Services\AthenaAPI\Calls;
use App\ValueObjects\Athena\Patient;
use App\ValueObjects\Athena\Problem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AthenaApiCallsTest extends TestCase
{
    use WithFaker;

    private $api;
    private $athenaPatientId;
    private $fakePatient;
    private $athenaDepartmentId;
    private $athenaPracticeId;

    public function test_it_gets_problems()
    {
        $problem1        = $this->addProblem($this->fakeProblem(234347009, $this->athenaPatientId, $this->athenaPracticeId));
        $problem2        = $this->addProblem($this->fakeProblem(195949008, $this->athenaPatientId, $this->athenaPracticeId));

        $response = $this->api->getPatientProblems($this->athenaPatientId, $this->athenaPracticeId, $this->athenaDepartmentId);

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
        $response = $this->api->getPatientProblems($this->athenaPatientId, $this->athenaPracticeId, $this->athenaDepartmentId);

        $this->assertTrue(is_array($response));
        $this->assertEquals([], $response['problems']);
        $this->assertEquals(0, $response['totalcount']);
    }

//    public function test_it_gets_patient_existing_appointments(){
//
//    }

//    public function test_it_gets_patient_new_appointments()
//    {
//        $appointment = $this->createNewAthenaAppointment();
//
//        $response = $this->api->getPatientAppointments($this->athenaPracticeId, $this->athenaPatientId);
//
//        $this->assertTrue(is_array($response));
//
//
//    }

    protected function setUp()
    {
        parent::setUp();

        $this->api             = new Calls();

        $this->athenaPracticeId = 195900;
        $this->athenaDepartmentId = 1;
        $this->fakePatient     = $this->fakePatient();
        $this->athenaPatientId = $this->createAthenaApiPatient($this->fakePatient);
    }

    private function fakeProblem($snomedCode, $athenaPatientId, $athenaPracticeId) {
        $problem = new Problem();
        $problem->setDepartmentId(1);
        $problem->setPracticeId($athenaPracticeId);
        $problem->setPatientId($athenaPatientId);
        $problem->setSnomedCode($snomedCode);
        $problem->setStatus('CHRONIC');

        return $problem;
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

    private function createAthenaApiPatient()
    {
        $patients = $this->api->createNewPatient($this->fakePatient);

        if (array_key_exists(0, $patients)) {
            $this->assertTrue(true);

            return $patients[0]['patientid'];
        }

        $this->assertTrue(false);
    }


    private function createAppointmentSlot(){

        $providerId = '86';
        $reasonId = '962';
        $appointmentDate = Carbon::now()->addMonth()->toDateString();
        $appointmentTime = '11:00';


        $response = $this->api->createNewAppointmentSlot($this->athenaPracticeId,
            $this->athenaDepartmentId,
            $providerId,
            $reasonId,
            $appointmentDate,
            $appointmentTime);

        if (array_key_exists(0, $response)) {
            $this->assertTrue(true);

            //need only the key TODO
            return $response['appointmentids'];
        }






    }

    private function createNewAthenaAppointment(){

        $providerId = '86';
        $reasonId = '962';

        $appointmentId = $this->createAppointmentSlot();

        $response = $this->api->createNewAppointment($this->athenaPracticeId,
            $this->athenaDepartmentId,
            $this->athenaPatientId,
            $providerId,
            $appointmentId,
            $reasonId);

        if (array_key_exists(0, $response)) {
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


}