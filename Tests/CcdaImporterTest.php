<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Tests;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachBillingProvider;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachDefaultPatientContactWindows;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachLocation;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\AttachPractice;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportAllergies;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportInsurances;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportMedications;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportProblems;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeCalvaryCcda;
use CircleLinkHealth\Eligibility\Tests\Fakers\FakeDiabetesAndEndocrineCcda;
use Tests\CustomerTestCase;

class CcdaImporterTest extends CustomerTestCase
{
    public function test_it_attaches_default_contact_windows()
    {
        $ccda = FakeCalvaryCcda::create();

        AttachDefaultPatientContactWindows::for($this->patient(), $ccda);

        $this->assertTrue(
            $this->patient()->patientInfo->contactWindows()->pluck('day_of_week')->all() === [1, 2, 3, 4, 5]
        );
    }

    public function test_it_attaches_location()
    {
        $ccda = FakeCalvaryCcda::create(['location_id' => $this->location()->id]);

        AttachLocation::for($this->patient(), $ccda);

        $this->assertTrue($this->patient()->locations()->where('locations.id', $this->location()->id)->exists());
    }

    public function test_it_attaches_practice()
    {
        $differentPracticeId = Practice::where('id', '!=', $this->practice()->id)->value('id');

        $ccda = FakeCalvaryCcda::create(['practice_id' => $differentPracticeId]);

        AttachPractice::for($this->patient(), $ccda);

        $this->assertTrue($this->patient()->program_id === $differentPracticeId);
    }

    public function test_it_does_not_import_ccd_without_practice_id()
    {
        $ccda = FakeDiabetesAndEndocrineCcda::create()->import();
        $this->assertTrue($ccda->validation_checks->has('program_id'));
    }

    public function test_it_imports_csv_ccda_allergies()
    {
        $ccda = FakeCalvaryCcda::create();

        ImportAllergies::for($this->patient(), $ccda);

        $allergies = $this->patient()->ccdAllergies()->get();

        $this->assertCount(1, $allergies);
        $this->assertTrue('macrodantin' === $allergies->first()->allergen_name);
    }

    public function test_it_imports_csv_ccda_billing_provider()
    {
        $ccda = FakeCalvaryCcda::create(['billing_provider_id' => $this->provider()->id]);

        AttachBillingProvider::for($this->patient(), $ccda);

        $this->assertTrue($this->provider()->id === $this->patient()->billingProviderUser()->id);
    }

    public function test_it_imports_csv_ccda_medications()
    {
        $ccda = FakeCalvaryCcda::create();

        ImportMedications::for($this->patient(), $ccda);

        $meds = $this->patient()->ccdMedications()->get();

        $this->assertCount(18, $meds);
    }

    public function test_it_imports_csv_ccda_problems()
    {
        $ccda = FakeCalvaryCcda::create();

        ImportProblems::for($this->patient(), $ccda);

        $problems = $this->patient()->ccdProblems()->get();

        $this->assertCount(18, $problems);
    }

    public function test_it_imports_insurances()
    {
        $ccda = FakeCalvaryCcda::create();

        ImportInsurances::for($this->patient(), $ccda);

        $insurances = $this->patient()->ccdInsurancePolicies;

        $this->assertCount(2, $insurances);

        $this->assertDatabaseHas(
            'ccd_insurance_policies',
            [
                'name'       => 'MEDICARE Part A',
                'type'       => 'primary_insurance',
                'policy_id'  => null,
                'relation'   => null,
                'subscriber' => null,
                'approved'   => false,
            ]
        );

        $this->assertDatabaseHas(
            'ccd_insurance_policies',
            [
                'name'       => 'Test Secondary Insurance',
                'type'       => 'secondary_insurance',
                'policy_id'  => null,
                'relation'   => null,
                'subscriber' => null,
                'approved'   => false,
            ]
        );
    }

    public function test_it_imports_patient_info()
    {
        $ccda = FakeCalvaryCcda::create();

        ImportPatientInfo::for($this->patient(), $ccda);

        $this->assertDatabaseHas('patient_info', [
            'birth_date'                 => '1950-01-01',
            'ccm_status'                 => Patient::ENROLLED,
            'consent_date'               => now()->toDateString(),
            'gender'                     => 'F',
            'mrn_number'                 => 'fake-record-12345212',
            'preferred_contact_language' => 'EN',
            'preferred_contact_method'   => 'CCT',
            'preferred_calls_per_month'  => 2,
            'daily_contact_window_start' => '09:00:00',
            'daily_contact_window_end'   => '18:00:00',
        ]);
    }

    public function test_it_imports_phones()
    {
        $ccda = FakeCalvaryCcda::create();

        $this->patient()->phoneNumbers()->delete();
        $this->assertEmpty($this->patient()->phoneNumbers()->get());

        ImportPhones::for($this->patient(), $ccda);

        $this->assertTrue(1 === $this->patient()->phoneNumbers()->count());

        $this->assertDatabaseHas('phone_numbers', [
            'user_id' => $this->patient()->id,
            'type'    => PhoneNumber::HOME,
            'number'  => '+12012819204',
        ]);
    }

    public function test_it_replaces_email_with_email_from_enrollee()
    {
        $enrollee = $this->enrollee();

        $ccda = FakeCalvaryCcda::create(['practice_id' => $enrollee->practice_id]);

        $imported = $ccda->fresh()->import($enrollee);
        $patient  = User::ofType('participant')->findOrFail($imported->patient_id);
        $this->assertTrue($patient->email === $enrollee->email);
    }

    public function test_it_when_survey_only_user_is_imported_user_role_changes_to_participant()
    {
        $enrollee          = $this->enrollee();
        $enrollee->user_id = $this->surveyOnly()->id;
        $enrollee->save();

        $ccda = FakeCalvaryCcda::create(['practice_id' => $enrollee->practice_id]);

        $imported = $ccda->fresh()->import($enrollee);
        $patient  = User::ofType('participant')->findOrFail($imported->patient_id);
        $this->assertTrue($patient->email === $enrollee->email);
        $this->assertFalse($patient->hasRole('survey-only'));
        $this->assertTrue($patient->hasRole('participant'));
    }
}
