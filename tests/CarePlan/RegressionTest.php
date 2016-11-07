<?php namespace Tests\CarePlan;

use Maknz\Slack\Facades\Slack;
use TestCase;
use Tests\Helpers\HandlesUsersAndCarePlans;


class RegressionTest extends TestCase
{
    use HandlesUsersAndCarePlans;

    protected $patients;

    protected $provider;

    /**
     * Test Manager
     * Just like your main() in java :joy:
     */
    public function testClhRegressionTesting()
    {
        $this->provider = $this->createUser();

        $this->userLogin($this->provider);

        $this->patients[] = $this->createNewPatient();

        //We use this so add a random number of CpmEntities for users after the first one.
        //In other words, the first User has all conditions, the rest can have any number of conditions.
        $i = 1;

        foreach ($this->patients as $patient) {

            //If we pass the first iteration, then choose up to 15 CpmEntities randomly
            //If the CpmEntity we are working with has less than 15 items, then number of items - 1 will be selected
            $numberOfRowsToCreate = ($i > 1) ? rand(2, 15) : null;

            $this->fillCarePlan($patient, $numberOfRowsToCreate);

            $i++;
        }

        $this->report();

    }

    public function report()
    {
        /**
         * Report stuff
         */

        //This is kinda hacky.
        //We are checking which database is being used to figure out which environment we are on.
        //This is because when testing, the APP_ENV is set to 'testing'
        $db = env('DB_DATABASE');

        $text = "
            A Provider was created:
            login: {$this->provider->email}
            password: password
            ";

        foreach ($this->patients as $patient) {
            $text .= "
            A patient was created:
            id: {$patient->id}
            name: {$patient->display_name}
            ";
        }

        if (in_array($db, ['cpm_staging', 'cpm_testing', 'cpm_hotfix'])) {
            Slack::to('#qualityassurance')
                ->send($text);
        }

        echo $text;
    }

    public function testCreatePatientWithAllConditions()
    {
        $this->provider = $this->createUser();

        $this->userLogin($this->provider);

        $patient = $this->createNewPatient();
        $this->patients[] = $patient;
        $this->fillCarePlan($patient);

        $this->report();
    }
}