<?php
use App\CLH\Repositories\UserRepository;
use App\PatientCareTeamMember;
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

        $this->addPatientCareTeam();

        $this->fillCareplanPage1();

        echo "\nPatientId: {$this->patient->ID}\n PatientName: {$this->patient->display_name}";
        echo "\nProviderLogin: {$this->provider->user_email}\n ProviderPass: password";
    }

    public function createProvider()
    {
        $faker = Faker\Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $workPhone = \App\CLH\Facades\StringManipulation::formatPhoneNumber($faker->phoneNumber);

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

            //provider Info
            'prefix' => 'Dr',
            'qualification' => 'MD',
            'npi_number' => 1234567890,
            'specialty' => 'Unit Tester',

            //phones
            'home_phone_number' => $workPhone,
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

        $patientInfo = $patient->patientInfo()->first();
        $patientInfo->preferred_cc_contact_days = '1, 2, 3, 4, 5, 6, 7';
        $patientInfo->save();

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
            'preferred_cc_contact_days' => '1, 2, 3, 4, 5, 6, 7',
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
        //We cannot use the UI to add care team members
        //because jQuery spits out the HTML for each provider.
        //Therefore, we're gonna add a provider programmatically
        //and make sure they show up.

        $member = PatientCareTeamMember::create([
            'user_id' => $this->patient->ID,
            'member_user_id' => $this->provider->ID,
            'type' => PatientCareTeamMember::MEMBER,
        ]);

        $billing = PatientCareTeamMember::create([
            'user_id' => $this->patient->ID,
            'member_user_id' => $this->provider->ID,
            'type' => PatientCareTeamMember::BILLING_PROVIDER,
        ]);

        $lead = PatientCareTeamMember::create([
            'user_id' => $this->patient->ID,
            'member_user_id' => $this->provider->ID,
            'type' => PatientCareTeamMember::LEAD_CONTACT,
        ]);

        $sendAlerts = PatientCareTeamMember::create([
            'user_id' => $this->patient->ID,
            'member_user_id' => $this->provider->ID,
            'type' => PatientCareTeamMember::SEND_ALERT_TO,
        ]);

        $this
            ->actingAs($this->provider)
            ->visit("/manage-patients/{$this->patient->ID}/careplan/team")
            ->see('Edit Patient Care Team')
            ->click('#add-care-team-member')
            ->see('Billing Provider')
            ->see('Lead Contact')
            ->see('Send Alert')
            ->see($this->provider->display_name)
        ;
    }

    public function fillCareplanPage1()
    {
        /*
        * Problems
        */
        $this->fillCpmEntityUserValues(
            'cpmProblems',
            null,
            "/manage-patients/{$this->patient->ID}/careplan/sections/1",
            'Diagnosis / Problems to Monitor',
            'cpm_problem_id'
        );

        /*
         * Lifestyles
         */
        $this->fillCpmEntityUserValues(
            'cpmLifestyles',
            null,
            "/manage-patients/{$this->patient->ID}/careplan/sections/1",
            'Lifestyle to Monitor',
            'cpm_lifestyle_id'
        );


        /*
         * Medication Groups
         */
        $this->fillCpmEntityUserValues(
            'cpmMedicationGroups',
            null,
            "/manage-patients/{$this->patient->ID}/careplan/sections/1",
            'Medications to Monitor',
            'cpm_medication_group_id'
        );


        /*
         * Miscs
         */
        $this->fillCpmEntityUserValues(
            'cpmMiscs',
            1,
            "/manage-patients/{$this->patient->ID}/careplan/sections/1",
            null,
            'cpm_misc_id'
        );
    }

    /**
     * This function will populate our test User's CpmEntity relationships (problems, lifestyles, medication groups,
     * misc, biometrics and symptoms).
     *
     *
     * @param $relationship         //name of the relationship function eg. cpmProblems, cpmMiscs ... ...
     * @param null $page            //use this ONLY for Miscs. Since Miscs can appera on all CarePlan pages, we wanna
     *                                make sure we are only working with Miscs that live in the page we are filling
     * @param $url                  //the url we are visiting to populate the entity relationship
     * @param null $sectionTitle    //if there's a title that appears on the page we wanna make sure it's there 
     *                                eg. Symptoms to Monitor
     * @param $entityIdFieldName    //the entity's id column from the pivot table eg. cpm_problem_id
     */
    public function fillCpmEntityUserValues($relationship, $page = null, $url, $sectionTitle = null, $entityIdFieldName)
    {
        $carePlanTemplate = $this->patient->service()
            ->firstOrDefaultCarePlan($this->patient)
            ->carePlanTemplate()
            ->first();

        /*
         * Cpm Entity
         */
        $query = $carePlanTemplate
            ->{$relationship}();

        empty($page) ?: $query->wherePivot('page', $page);

        $carePlanEntities = $query->get();

        $this
            ->actingAs($this->provider)
            ->visit($url);

        empty($sectionTitle) ?: $this->see($sectionTitle);

        foreach ($carePlanEntities as $entity)
        {
            $this->select($entity->id, "{$relationship}[$entity->id]");
            $this->press('TestSubmit');

            $this->seeInDatabase("{$entity->getTable()}_users", [
                $entityIdFieldName => $entity->id,
                'patient_id' => $this->patient->ID,
                'cpm_instruction_id' => $entity->pivot->cpm_instruction_id,
            ]);
        }

        $patientEntities = $this->patient->{$relationship}()
            ->lists($entityIdFieldName)
            ->all();

        $this->assertEquals(count($patientEntities), count($carePlanEntities->all()));
    }
}