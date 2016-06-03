<?php
use App\CLH\Repositories\UserRepository;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;


class RegressionTest extends TestCase
{
//    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected $patient;
    protected $provider;

    public function testClhRegressionTesting()
    {
        /*
         * Since we're using DatabaseTransactions, it seems like Laravel rolls back after executing each method.
         * @todo: research what's going on and figure out if DatabaseTransactions can be rolled back after all tests run
         *
         * For let's just call it again.
         */
        $this->createProvider();

        $this->providerLogin();

        $this->createNewPatient();
        
//        $this->addPatientCareTeam();

        $this->fillCareplanPage1();

    }

    public function createProvider()
    {
        $faker = Faker\Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;

        $roles = [
            \App\Role::whereName('provider')->first()->id,
        ];

        $bag = new ParameterBag([
            'user_email' => $email,
            'user_pass' => 'password',
            'display_name' => "$firstName $lastName",
            'first_name' => $firstName,
            'last_name' => $lastName,
            'user_login' => $faker->userName,
            'program_id' => 9, //testdrive
            'address' => $faker->streetAddress,
            'address2' => '',
            'city' => $faker->city,
            'state' => 'AL',
            'zip' => '12345',
            'is_auto_generated' => true,
            'roles' => $roles,
        ]);

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        //check that it was created
        $this->seeInDatabase('users', ['user_email' => $email]);

        //check that the roles were created
        foreach ($roles as $role) {
            $this->seeInDatabase('lv_role_user', [
                'user_id' => $user->ID,
                'role_id' => $role,
            ]);
        }

        $this->provider = $user;
    }

    public function providerLogin()
    {
        $this->visit('/auth/login')
            ->see('CarePlanManager')
            ->type($this->provider->user_email, 'email')
            ->type('password', 'password')
            ->press('Log In')
            ->seePageIs('/manage-patients/dashboard');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

    public function createNewPatient()
    {
        $this->visit('/manage-patients/dashboard')
            ->see('Add a patient')
            ->click('add-patient')
            ->seePageIs('/manage-patients/careplan/demographics');


        $faker = Faker\Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $mrn = $faker->randomNumber(6);
        $genderCollection = ['F', 'M'];
        $gender = $genderCollection[array_rand($genderCollection, 1)];
        $languageCollection = ['EN', 'ES'];
        $language = $languageCollection[array_rand($languageCollection, 1)];
        $dob = $faker->date();
        $homePhone = \App\CLH\Facades\StringManipulation::formatPhoneNumber($faker->phoneNumber);
        $cellPhone = \App\CLH\Facades\StringManipulation::formatPhoneNumber($faker->phoneNumber);
        $email = $faker->email;
        $streetAddress = $faker->streetAddress;
        $city = $faker->city;
        $state = $faker->stateAbbr;
        $zip = $faker->postcode;
        $agentName = $faker->name;
        $agentPhone = \App\CLH\Facades\StringManipulation::formatPhoneNumber($faker->phoneNumber);
        $agentRelationship = 'Next of Kin';
        $agentEmail = $faker->email;
        $contactTime = '05:00 PM';
        $contactMethod = 'CCT';
        $consentDate = $faker->date();
        $timezone = 'America/New_York';
        $ccmStatus = 'enrolled';

        $this
            ->actingAs($this->provider)
            ->type($firstName, 'first_name')
            ->type($lastName, 'last_name')
            ->select($gender, 'gender')
            ->select($language, 'preferred_contact_language')
            ->type($mrn, 'mrn_number')
            ->type($dob, 'birth_date')
            ->type($homePhone, 'home_phone_number')
            ->type($cellPhone, 'mobile_phone_number')
            ->type($email, 'email')
            ->type($streetAddress, 'address')
            ->type($city, 'city')
            ->select($state, 'state')
            ->type($zip, 'zip')
            ->type($agentName, 'agent_name')
            ->type($agentPhone, 'agent_telephone')
            ->type($agentRelationship, 'agent_relationship')
            ->type($agentEmail, 'agent_email')
            ->type($agentEmail, 'agent_email')
            ->type($contactTime, 'preferred_contact_time')
//            ->click('#contact-days-3')
            ->select($contactMethod, 'preferred_contact_method')
            ->type($consentDate, 'consent_date')
            ->select($timezone, 'preferred_contact_timezone')
            ->select($ccmStatus, 'ccm_status')
            ->press('Add Patient')
            ->select(10, 'preferred_contact_location')
            ->press('TestSubmit');

        $this->seeInDatabase('users', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'user_email' => $email,
            'display_name' => "$firstName $lastName",
            'program_id' => 9,
            'address' => $streetAddress,
            'city' => $city,
            'state' => $state,
            'zip' => $zip
        ]);

        $patient = User::whereUserEmail($email)->first();

        $this->patient = $patient;

        $this->seeInDatabase('patient_info', [
            'user_id' => $this->patient->ID,
            'agent_name' => $agentName,
            'agent_telephone' => $agentPhone,
            'agent_email' => $agentEmail,
            'agent_relationship' => $agentRelationship,
            'birth_date' => $dob,
            'ccm_status' => $ccmStatus,
            'consent_date' => $consentDate,
            'gender' => $gender,
            'mrn_number' => $mrn,
            'preferred_contact_location' => 10,
            'preferred_contact_language' => $language,
            'preferred_contact_method' => $contactMethod,
            'preferred_contact_time' => $contactTime,
            'preferred_contact_timezone' => $timezone,
            'daily_reminder_optin' => 'Y',
            'daily_reminder_time' => '08:00',
            'daily_reminder_areas' => 'TBD',
            'hospital_reminder_optin' => 'Y',
            'hospital_reminder_time' => '19:00',
            'hospital_reminder_areas' => 'TBD',
            'careplan_status' => 'draft',
        ]);

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }
    
    public function addPatientCareTeam()
    {
        $this
            ->actingAs($this->provider)
            ->visit("/manage-patients/{$this->patient->ID}/careplan/team")
            ->see('Edit Patient Care Team')
            ->click('#add-care-team-member')
            ->select($this->provider->display_name, 'provider_id')
            ->see('Billing Provider')
            ->check('#ctm1bp') //billing provider html name attr
            ->see('Lead Contact')
            ->check('ctlc') //lead contact html name attr
            ->see('Send Alert')
            ->check('#ctm1sa') //send alert html name attr
        ;
    }

    public function fillCareplanPage1()
    {
        $carePlanTemplate = $this->patient->service()
            ->firstOrDefaultCarePlan($this->patient)
            ->carePlanTemplate()
            ->first();

        $carePlanProblems = $carePlanTemplate->cpmProblems()
            ->lists('cpm_problem_id')
            ->all();
        
        $this
            ->actingAs($this->provider)
            ->visit("/manage-patients/{$this->patient->ID}/careplan/sections/1")
            ->see('Diagnosis / Problems to Monitor');
        
        foreach ($carePlanProblems as $problemId)
        {
            $this->select($problemId, "cpmProblems[$problemId]");
            $this->press('TestSubmit');

            $this->seeInDatabase('cpm_problems_users', [
                'cpm_problem_id' => $problemId,
                'patient_id' => $this->patient->ID,
            ]);
        }



    }
}