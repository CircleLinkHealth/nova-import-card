<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Helpers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CpmWeight;
use Faker\Factory;
use Laravel\Dusk\Browser;

trait CarePlanHelpers
{
    public function addPatientCareTeam($patient)
    {
        //We cannot use the UI to add care team members
        //because jQuery spits out the HTML for each provider.
        //Therefore, we're gonna add a provider programmatically
        //and make sure they show up.

        $member = CarePerson::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::MEMBER,
        ]);

        $billing = CarePerson::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::BILLING_PROVIDER,
        ]);

        $lead = CarePerson::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::LEAD_CONTACT,
        ]);

        $sendAlerts = CarePerson::create([
            'user_id'        => $patient->id,
            'member_user_id' => $this->provider->id,
            'type'           => CarePerson::SEND_ALERT_TO,
        ]);
    }

    public function createNewPatient()
    {
        $newPatient = $this->getNewPatientObject();

        $this->browse(function (Browser $browser) use ($newPatient) {
            $browser->loginAs($this->provider)
                ->visit(route('patients.dashboard'))
                ->assertPathIs('/manage-patients/dashboard')
                ->assertSee('Add a Patient')
                ->click('@add-patient-btn')
                ->assertPathIs('/manage-patients/careplan/demographics')
                ->type('first_name', $newPatient->firstName)
                ->type('last_name', $newPatient->lastName)
                ->radio('#male', 'M')
//                    ->radio('preferred_contact_language', $newPatient->language)
                ->type('mrn_number', $newPatient->mrn)
//                    ->type('birth_date', $newPatient->dob)
                ->type('home_phone_number', $newPatient->homePhone)
                ->type('mobile_phone_number', $newPatient->cellPhone)
                ->type('email', $newPatient->email)
                ->type('address', $newPatient->streetAddress)
                ->type('city', $newPatient->city)
                ->select('state', $newPatient->state)
                ->type('zip', $newPatient->zip)
                ->type('agent_name', $newPatient->agentName)
                ->type('agent_telephone', $newPatient->agentPhone)
                ->type('agent_relationship', $newPatient->agentRelationship)
                ->type('agent_email', $newPatient->agentEmail)
                ->type('agent_email', $newPatient->agentEmail)
//                    ->select($newPatient->contactMethod, 'preferred_contact_method')
//                    ->type('consent_date', $newPatient->consentDate)
                ->select('timezone', $newPatient->timezone)
                ->select('ccm_status', $newPatient->ccmStatus)
                ->press('Add Patient')
                ->press('TestSubmit')
                ->select(10, 'preferred_contact_location')
                ->select([
                    1,
                    2,
                    3,
                    4,
                    5,
                ], 'days')
                ->type($newPatient->windowTimeStart, 'window_start')
                ->type($newPatient->windowTimeEnd, 'window_end')
                ->press('@unit-test-submit');
        });

        $this->assertDatabaseHas('users', [
            'first_name'   => $newPatient->firstName,
            'last_name'    => $newPatient->lastName,
            'email'        => $newPatient->email,
            'display_name' => "$newPatient->firstName $newPatient->lastName",
            'program_id'   => 9,
            'address'      => $newPatient->streetAddress,
            'city'         => $newPatient->city,
            'state'        => $newPatient->state,
            'zip'          => $newPatient->zip,
            'timezone'     => $newPatient->timezone,
        ]);

        $patient = User::whereEmail($newPatient->email)->first();

        $this->assertDatabaseHas('location_user', [
            'location_id' => 10,
            'user_id'     => $patient->id,
        ]);

        $ccda = \CircleLinkHealth\SharedModels\Entities\Ccda::create([
            'user_id'   => $patient->id,
            'vendor_id' => 1,
            'source'    => 'test',
            'xml'       => 'test',
            'json'      => 'test',
        ]);

        factory(\CircleLinkHealth\SharedModels\Entities\CcdInsurancePolicy::class, 3)->create([
            'patient_id' => $patient->id,
            'ccda_id'    => $ccda->id,
        ]);

        $patientInfo = $patient->patientInfo;

        for ($i = 1; $i < 6; ++$i) {
            $this->assertDatabaseHas('patient_contact_window', [
                'patient_info_id'   => $patientInfo->id,
                'day_of_week'       => $i,
                'window_time_start' => $newPatient->windowTimeStart,
                'window_time_end'   => $newPatient->windowTimeEnd,
            ]);
        }

        $this->assertDatabaseHas('patient_info', [
            'user_id'                    => $patient->id,
            'agent_name'                 => $newPatient->agentName,
            'agent_telephone'            => $newPatient->agentPhone,
            'agent_email'                => $newPatient->agentEmail,
            'agent_relationship'         => $newPatient->agentRelationship,
            'birth_date'                 => $newPatient->dob,
            'ccm_status'                 => $newPatient->ccmStatus,
            'consent_date'               => $newPatient->consentDate,
            'gender'                     => $newPatient->gender,
            'mrn_number'                 => $newPatient->mrn,
            'preferred_contact_location' => 10,
            'preferred_contact_language' => $newPatient->language,
            'preferred_contact_method'   => $newPatient->contactMethod,
            'daily_reminder_optin'       => 'Y',
            'daily_reminder_time'        => '08:00',
            'daily_reminder_areas'       => 'TBD',
            'hospital_reminder_optin'    => 'Y',
            'hospital_reminder_time'     => '19:00',
            'hospital_reminder_areas'    => 'TBD',
        ]);

        $this->assertDatabaseHas('care_plans', [
            'user_id' => $patient->id,
            'status'  => 'draft',
        ]);

        return $patient;
    }

    public function fillBiometrics(User $patient)
    {
        if (count($patient->cpmBiometrics()->where('type', 0)->first())) {
            $weight = factory(CpmWeight::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 1)->first())) {
            $bloodPressure = factory(\CircleLinkHealth\SharedModels\Entities\CpmBloodPressure::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 2)->first())) {
            $bloodSugar = factory(\CircleLinkHealth\SharedModels\Entities\CpmBloodSugar::class)->create([
                'patient_id' => $patient->id,
            ]);
        }

        if (count($patient->cpmBiometrics()->where('type', 3)->first())) {
            $smoking = factory(\CircleLinkHealth\SharedModels\Entities\CpmSmoking::class)->create([
                'patient_id' => $patient->id,
            ]);
        }
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

    public function fillCareplanPage1(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        // Problems
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmProblems',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Diagnosis / Problems to Monitor',
            'cpm_problem_id',
            $numberOfRowsToCreate
        );

        // Lifestyles
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmLifestyles',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Lifestyle to Monitor',
            'cpm_lifestyle_id',
            $numberOfRowsToCreate
        );

        // Medication Groups
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmMedicationGroups',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/1",
            'Medications to Monitor',
            'cpm_medication_group_id',
            $numberOfRowsToCreate
        );

        // Miscs
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

    public function fillCareplanPage2(
        User $patient,
        $numberOfRowsToCreate = null
    ) {
        // Biometrics
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
        // Symptoms
        $this->fillCpmEntityUserValues(
            $patient,
            'cpmSymptoms',
            null,
            "/manage-patients/{$patient->id}/careplan/sections/3",
            'Symptoms to Monitor',
            'cpm_symptom_id',
            $numberOfRowsToCreate
        );

        // Additional Information
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

    /**
     * This function will populate our test User's CpmEntity relationships (problems, lifestyles, medication groups,
     * misc, biometrics and symptoms).
     *
     * @param $relationship //name of the relationship function eg. cpmProblems, cpmMiscs ... ...
     * @param null $page //use this ONLY for Miscs. Since Miscs can appera on all CarePlan pages, we wanna
     *                   make sure we are only working with Miscs that live in the page we are filling
     * @param $url //the url we are visiting to populate the entity relationship
     * @param null $sectionTitle //if there's a title that appears on the page we wanna make sure it's there
     *                           eg. Symptoms to Monitor
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

        // Cpm Entity
        $query = $carePlanTemplate
            ->{$relationship}();

        empty($page)
            ?: $query->wherePivot('page', $page);
        $carePlanEntities = $query->get();

        if ( ! empty($numberOfRowsToCreate)) {
            if ($numberOfRowsToCreate > $carePlanEntities->count()) {
                $numberOfRowsToCreate = rand(2, $carePlanEntities->count() - 1);
            }

            $carePlanEntities = $carePlanEntities->random($numberOfRowsToCreate);
        }

        $this
            ->be($this->provider)
            ->visit($url);

        foreach ($carePlanEntities as $entity) {
            $this->select($entity->id, "{$relationship}[$entity->id]");

            $this->press('TestSubmit');

            $this->assertDatabaseHas("{$entity->getTable()}_users", [
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

    public function getNewPatientObject()
    {
        $faker = Factory::create();

        $object = new \stdClass();

        $object->firstName = $faker->firstName;
        $object->lastName  = $faker->lastName;
        $object->mrn       = $faker->randomNumber(6);
        $genderCollection  = [
            'F',
            'M',
        ];
        $object->gender      = $genderCollection[array_rand($genderCollection, 1)];
        $object->genderRadio = 'M' == $object->gender
            ? '@male-gender'
            : '@female-gender';
        $languageCollection = [
            'EN',
            'ES',
        ];
        $object->language          = $languageCollection[array_rand($languageCollection, 1)];
        $object->dob               = $faker->date();
        $object->homePhone         = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($faker->phoneNumber);
        $object->cellPhone         = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($faker->phoneNumber);
        $object->email             = $faker->email;
        $object->streetAddress     = $faker->streetAddress;
        $object->city              = $faker->city;
        $object->state             = $faker->stateAbbr;
        $object->zip               = $faker->postcode;
        $object->agentName         = $faker->name;
        $object->agentPhone        = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumber($faker->phoneNumber);
        $object->agentRelationship = 'Next of Kin';
        $object->agentEmail        = $faker->email;
        $object->contactMethod     = 'CCT';
        $object->consentDate       = $faker->date();
        $object->timezone          = 'America/New_York';
        $object->ccmStatus         = 'enrolled';
        $object->windowTimeStart   = '09:00';
        $object->windowTimeEnd     = '19:00';

        return $object;
    }

    public function printCarePlanTest(User $patient)
    {
        $billingProvider = User::find($patient->getBillingProviderID());
        $today           = Carbon::now()->toFormattedDateString();

        $this->be($this->provider);

        $this->browse(function (Browser $browser) use ($patient, $billingProvider, $today) {
            $browser->visit("/manage-patients/{$patient->id}/careplan/sections/3")
                ->click('approve-forward')
                ->assertPathIs("/manage-patients/{$patient->id}/view-careplan?page=3")
                ->assertSee('Care Plan')
                ->assertSee($patient->getFullName())
                ->assertSee($patient->getPhone())
                ->assertSee($today)
                ->assertSee($billingProvider->getFullName())
                ->assertSee($billingProvider->getPhone());
        });

        // Check that entities are on the page
        $this->seeUserEntityNameOnPage($patient, 'cpmProblems');
        $this->seeUserEntityNameOnPage($patient, 'cpmMedicationGroups');
        $this->seeUserEntityNameOnPage($patient, 'cpmSymptoms');
        $this->seeUserEntityNameOnPage($patient, 'cpmLifestyles');
        $this->seeUserEntityNameOnPage($patient, 'cpmBiometrics', [
            'Smoking (# per day)',
        ]);
    }

    public function report()
    {
        /**
         * Report stuff.
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
        ])) {
            sendSlackMessage('#qualityassurance', $text);
        } else {
            sendSlackMessage('#background-tasks-dev', $text);
        }

        echo $text;
    }

    /**
     * Check that the User's CpmEntities appear on the page.
     *
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

            $response->assertSee($entity->name);
        }
    }
}
