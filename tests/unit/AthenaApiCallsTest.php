<?php

namespace Tests\Unit;

use App\Enrollee;
use App\Services\AthenaAPI\Calls;
use App\ValueObjects\Athena\Patient;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AthenaApiCallsTest extends TestCase
{
    use WithFaker;

    protected $api;
    protected $athenaPatientId;
    protected $fakePatient;

    public function test_it_gets_problems()
    {
        $response = $this->api->getPatientProblems();

        $this->assertTrue(is_array($response));
        $this->assertArrayHasKey('problems', $response);
    }

    public function test_it_gets_zero_problems()
    {
        //make a call to ad
        $response = $this->api->getPatientProblems();

        $this->assertTrue(is_array($response));
        $this->assertEquals([], $response);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->api             = new Calls();
        $this->fakePatient     = $this->fakePatient();
        $this->athenaPatientId = $this->createAthenaApiPatient($this->fakePatient);
    }

    public function fakePatient()
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
        $patient->setHomePhone($this->faker()->phoneNumber);
        $patient->setMobilePhone($this->faker()->phoneNumber);

        return $patient;
    }

    public function createAthenaApiPatient()
    {
        $patients = $this->api->createNewPatient($this->fakePatient);

        if (array_key_exists(0, $patients)) {
            $this->athenaPatientId = $patients[0]['patientid'];
        }
    }


}