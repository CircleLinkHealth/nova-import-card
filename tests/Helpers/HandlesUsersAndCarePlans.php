<?php namespace Tests\Helpers;

use App\CLH\Facades\StringManipulation;
use App\CLH\Repositories\UserRepository;
use App\Models\CPM\Biometrics\CpmWeight;
use App\PatientCareTeamMember;
use App\Practice;
use App\Role;
use App\User;
use Carbon\Carbon;
use Faker\Factory;
use Symfony\Component\HttpFoundation\ParameterBag;

trait HandlesUsersAndCarePlans
{
    /**
     * @param int $programId
     * @param string $roleName
     *
     * @return $this|User
     */
    public function createUser(
        $programId = 9,
        $roleName = 'provider'
    ) : User
    {
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $workPhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);

        $roles = [
            Role::whereName($roleName)->first()->id,
        ];

        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => 'password',
            'display_name'      => "$firstName $lastName",
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'username'          => $faker->userName,
            'program_id'        => $programId,
            //id=9 is testdrive
            'address'           => $faker->streetAddress,
            'address2'          => '',
            'city'              => $faker->city,
            'state'             => 'AL',
            'zip'               => '12345',
            'is_auto_generated' => true,
            'roles'             => $roles,
            'timezone'          => 'America/New_York',

            //provider Info
            'prefix'            => 'Dr',
            'qualification'     => 'MD',
            'npi_number'        => 1234567890,
            'specialty'         => 'Unit Tester',

            //phones
            'home_phone_number' => $workPhone,
        ]);

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        $locations = Practice::find($programId)->locations
            ->pluck('id')
            ->all();

        $user->locations()->sync($locations);

        foreach ($locations as $locId) {
            $this->seeInDatabase('location_user', [
                'location_id' => $locId,
                'user_id'     => $user->id,
            ]);
        }

        //check that it was created
        $this->seeInDatabase('users', ['email' => $email]);

        //check that the roles were created
        foreach ($roles as $role) {
            $this->seeInDatabase('lv_role_user', [
                'user_id' => $user->id,
                'role_id' => $role,
            ]);
        }

        return $user;
    }

    public function userLogin(User $provider)
    {
        $this->visit('/auth/login')
            ->see('CarePlanManager')
            ->type($provider->email, 'email')
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


        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $mrn = $faker->randomNumber(6);
        $genderCollection = [
            'F',
            'M',
        ];
        $gender = $genderCollection[array_rand($genderCollection, 1)];
        $languageCollection = [
            'EN',
            'ES',
        ];
        $language = $languageCollection[array_rand($languageCollection, 1)];
        $dob = $faker->date();
        $homePhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);
        $cellPhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);
        $email = $faker->email;
        $streetAddress = $faker->streetAddress;
        $city = $faker->city;
        $state = $faker->stateAbbr;
        $zip = $faker->postcode;
        $agentName = $faker->name;
        $agentPhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);
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
            //->type($contactTime, 'preferred_contact_time')
            ->select($contactMethod, 'preferred_contact_method')
            ->type($consentDate, 'consent_date')
            ->select($timezone, 'timezone')
            ->select($ccmStatus, 'ccm_status')
            ->press('Add Patient')
            ->press('TestSubmit')
            ->select(10, 'preferred_contact_location')
            ->press('TestSubmit');

        $this->seeInDatabase('users', [
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'display_name' => "$firstName $lastName",
            'program_id'   => 9,
            'address'      => $streetAddress,
            'city'         => $city,
            'state'        => $state,
            'zip'          => $zip,
            'timezone'     => $timezone,
        ]);

        $patient = User::whereEmail($email)->first();

        $this->seeInDatabase('location_user', [
            'location_id' => 10,
            'user_id'     => $patient->id,
        ]);

//        $ccda = \App\Models\CCD\Ccda::create([
//            'user_id' => $patient->id,
//            'vendor_id' => 1,
//            'source' => 'test',
//            'xml' => 'test',
//            'json' => 'test',
//        ]);
//
//        factory(\App\Models\CCD\CcdInsurancePolicy::class, 3)->create([
//            'patient_id' => $patient->id,
//            'ccda_id' => $ccda->id,
//        ]);

        $patientInfo = $patient->patientInfo()->first();
        $patientInfo->preferred_cc_contact_days = '1, 2, 3, 4, 5, 6, 7';
        $patientInfo->save();

        $this->seeInDatabase('patient_info', [
            'user_id'                    => $patient->id,
            'agent_name'                 => $agentName,
            'agent_telephone'            => $agentPhone,
            'agent_email'                => $agentEmail,
            'agent_relationship'         => $agentRelationship,
            'birth_date'                 => $dob,
            'ccm_status'                 => $ccmStatus,
            'consent_date'               => $consentDate,
            'gender'                     => $gender,
            'mrn_number'                 => $mrn,
            //            'preferred_cc_contact_days' => '1, 2, 3, 4, 5, 6, 7',
            'preferred_contact_location' => 10,
            'preferred_contact_language' => $language,
            'preferred_contact_method'   => $contactMethod,
            //            'preferred_contact_time' => $contactTime,
            //            'preferred_contact_timezone' => $timezone,
            'daily_reminder_optin'       => 'Y',
            'daily_reminder_time'        => '08:00',
            'daily_reminder_areas'       => 'TBD',
            'hospital_reminder_optin'    => 'Y',
            'hospital_reminder_time'     => '19:00',
            'hospital_reminder_areas'    => 'TBD',
            'careplan_status'            => 'draft',
        ]);

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();


        return $patient;
    }

    public function fillCarePlan(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        $this->addPatientCareTeam($patient);

        $this->fillCareplanPage1($patient, $numberOfRowsToCreate);

        $this->fillCareplanPage2($patient, $numberOfRowsToCreate);

        $this->fillCareplanPage3($patient, $numberOfRowsToCreate);

        $this->fillBiometrics($patient);

        $this->printCarePlanTest($patient);
    }

    public function addPatientCareTeam($patient)
    {
        //We cannot use the UI to add care team members
        //because jQuery spits out the HTML for each provider.
        //Therefore, we're gonna add a provider programmatically
        //and make sure they show up.

        $member = PatientCareTeamMember::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => PatientCareTeamMember::MEMBER,
        ]);

        $billing = PatientCareTeamMember::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => PatientCareTeamMember::BILLING_PROVIDER,
        ]);

        $lead = PatientCareTeamMember::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => PatientCareTeamMember::LEAD_CONTACT,
        ]);

        $sendAlerts = PatientCareTeamMember::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => PatientCareTeamMember::SEND_ALERT_TO,
        ]);

        $this
            ->actingAs($this->provider)
            ->visit("/manage-patients/{$patient->id}/careplan/team")
            ->see('Edit Patient Care Team')
            ->click('#add-care-team-member')
            ->see('Billing Provider')
            ->see('Lead Contact')
            ->see('Send Alert')
            ->see($this->provider->display_name);
    }

    public function fillCareplanPage1(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        /*
        * Problems
        */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmProblems',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Diagnosis / Problems to Monitor',
            'cpm_problem_id',
            $numberOfRowsToCreate
        );

        /*
         * Lifestyles
         */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmLifestyles',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Lifestyle to Monitor',
            'cpm_lifestyle_id',
            $numberOfRowsToCreate
        );


        /*
         * Medication Groups
         */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmMedicationGroups',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Medications to Monitor',
            'cpm_medication_group_id',
            $numberOfRowsToCreate
        );


        /*
         * Miscs
         */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmMiscs',
            1,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            null,
            'cpm_misc_id',
            $numberOfRowsToCreate
        );
    }

    /**
     * This function will populate our test User's CpmEntity relationships (problems, lifestyles, medication groups,
     * misc, biometrics and symptoms).
     *
     *
     * @param $relationship //name of the relationship function eg. cpmProblems, cpmMiscs ... ...
     * @param null $page //use this ONLY for Miscs. Since Miscs can appera on all CarePlan pages, we wanna
     *                                make sure we are only working with Miscs that live in the page we are filling
     * @param $url //the url we are visiting to populate the entity relationship
     * @param null $sectionTitle //if there's a title that appears on the page we wanna make sure it's there
     *                                eg. Symptoms to Monitor
     * @param $entityIdFieldName //the entity's id column from the pivot table eg. cpm_problem_id
     * @param $numberOfRowsToCreate //how many of those entities should be associated with the user. The default is
     *                                null, and that means relate all entities
     */
    public function fillCpmEntityUserValues(
        User $patient,
        $relationship,
        $page = null,
        $url,
        $sectionTitle = null,
        $entityIdFieldName,
        $numberOfRowsToCreate = null
    ) {
        $carePlanTemplate = $patient->service()
            ->firstOrDefaultCarePlan($patient)
            ->carePlanTemplate()
            ->first();

        /*
         * Cpm Entity
         */
        $query = $carePlanTemplate
            ->{$relationship}();

        empty($page)
            ?: $query->wherePivot('page', $page);
        $carePlanEntities = $query->get();

        if (!empty($numberOfRowsToCreate)) {
            if ($numberOfRowsToCreate > $carePlanEntities->count()) {
                $numberOfRowsToCreate = rand(2, $carePlanEntities->count() - 1);
            }

            $carePlanEntities = $carePlanEntities->random($numberOfRowsToCreate);

            if (is_object($carePlanEntities)) {
                $carePlanEntities = collect($carePlanEntities);
            }
        }

        $this
            ->actingAs($this->provider)
            ->visit($url);

        empty($sectionTitle)
            ?: $this->see($sectionTitle);

        foreach ($carePlanEntities as $entity) {
            $this->select($entity->id, "{$relationship}[$entity->id]");

            $this->press('TestSubmit');

            $this->seeInDatabase("{$entity->getTable()}_users", [
                $entityIdFieldName   => $entity->id,
                'patient_id'         => $patient->id,
                'cpm_instruction_id' => $entity->pivot->cpm_instruction_id,
            ]);
        }

        $patientEntities = $patient
            ->{$relationship}()
            ->pluck($entityIdFieldName)
            ->all();

        /*
         * This is kinda hacky.
         * We are checking if the $patientEntities >= $carePlanEntities.
         * We are interested that the patient has at least as many entities as the ones that were activated.
         * We are checking for >=, instead of just ==, because in the case of Miscs a patient will have 6 miscs,
         * but on page 3 there are only 4 Miscs, so the patient will always have 2 more miscs, which are the miscs
         * that appear on the first page.
         * Instead of putting effort into only picking the UserMiscs from the 3rd page, we are just gonna expect that
         * the patient will have more miscs that page 3 of the care plan.
         * Hope this makes sense in the future :)
         */
        $this->assertGreaterThanOrEqual(count($carePlanEntities->all()), count($patientEntities));

    }

    public function fillCareplanPage2(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        /*
        * Biometrics
        */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmBiometrics',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/2",
            'Biometrics to Monitor',
            'cpm_biometric_id',
            $numberOfRowsToCreate
        );
    }

    public function fillCareplanPage3(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        /*
        * Symptoms
        */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmSymptoms',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/3",
            'Symptoms to Monitor',
            'cpm_symptom_id',
            $numberOfRowsToCreate
        );

        /*
        * Additional Information
        */
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmMiscs',
            3,
            "/manage-patients/{$patient->id}/careplan/sections/3",
            'Additional Information',
            'cpm_misc_id',
            $numberOfRowsToCreate
        );
    }

    public function fillBiometrics(User $patient)
    {
        if (count($patient->cpmBiometrics()->where('type', 0)->first())) {
            $weight = factory(CpmWeight::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 1)->first())) {
            $bloodPressure = factory(\App\Models\CPM\Biometrics\CpmBloodPressure::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 2)->first())) {
            $bloodSugar = factory(\App\Models\CPM\Biometrics\CpmBloodSugar::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 3)->first())) {
            $smoking = factory(\App\Models\CPM\Biometrics\CpmSmoking::class)->create([
                'patient_id' => $patient->id,
            ]);
        }
    }

    public function printCarePlanTest(User $patient)
    {
        $billingProvider = User::find($patient->getBillingProviderIDAttribute());
        $today = Carbon::now()->toFormattedDateString();

        $this->actingAs($this->provider)
            ->visit("/manage-patients/{$patient->id}/careplan/sections/3")
            ->click('approve-forward')
            ->seePageIs("/manage-patients/{$patient->id}/view-careplan?page=3")
            ->see('Care Plan')
            ->see($patient->fullName)
            ->see($patient->phone)
            ->see($today)
            ->see($billingProvider->fullName)
            ->see($billingProvider->phone);

        /**
         * Check that entities are on the page
         */
        $this->seeUserEntityNameOnPage($patient, 'cpmProblems');
        $this->seeUserEntityNameOnPage($patient, 'cpmMedicationGroups');
        $this->seeUserEntityNameOnPage($patient, 'cpmSymptoms');
        $this->seeUserEntityNameOnPage($patient, 'cpmLifestyles');
        $this->seeUserEntityNameOnPage($patient, 'cpmBiometrics', [
            'Smoking (# per day)',
        ]);
    }

    /**
     * Check that the User's CpmEntities appear on the page.
     *
     * @param User $patient
     * @param $relationship //for example 'cpmProblems'
     * @param array $exclude //names to exclude
     */
    public function seeUserEntityNameOnPage(
        User $patient,
        $relationship,
        array $exclude = []
    ) {
        $patientEntities = $patient->{$relationship}()->get();

        foreach ($patientEntities as $entity) {
            if (in_array($entity->name, $exclude)) {
                continue;
            }

            $this->see($entity->name);
        }
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

        if (in_array($db, [
            'cpm_staging',
            'cpm_testing',
            'cpm_hotfix',
        ])) {
//            Slack::to('#qualityassurance')
//                ->send($text);
        }

        echo $text;
    }
}