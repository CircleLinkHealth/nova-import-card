<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\AppConfig;
use App\Models\CPM\CpmProblem;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\PracticesRequiringBhiConsent;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class BHIReconsentTest extends TestCase
{
    use UserHelpers;

    public function test_it_is_bhi_for_after_cutoff_consent_date()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, false);

        $this->assertTrue($bhiPatient->isBhi());
    }

    public function test_it_is_bhi_for_after_cutoff_consent_date_and_practice_requires_consent_and_patient_consented()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, true, false);
        AppConfig::create([
            'config_key'   => PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY,
            'config_value' => $bhiPractice->name, ]);

        $this->assertTrue($bhiPatient->isBhi());
    }

    public function test_it_is_bhi_for_before_cutoff_consent_date()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id);

        $this->assertTrue($bhiPatient->isBhi());
    }

    public function test_it_is_bhi_for_before_cutoff_consent_date_and_practice_requires_consent_and_patient_consented()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, true, true);
        AppConfig::create([
            'config_key'   => PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY,
            'config_value' => $bhiPractice->name, ]);

        $this->assertTrue($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_for_after_cutoff_consent_date_and_practice_requires_consent()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, false);
        AppConfig::create([
            'config_key'   => PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY,
            'config_value' => $bhiPractice->name, ]);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_for_before_cutoff_consent_date()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, true);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_for_before_cutoff_consent_date_and_practice_requires_consent()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, true);
        AppConfig::create([
            'config_key'   => PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY,
            'config_value' => $bhiPractice->name, ]);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_if_patient_does_not_have_bhi_consent_note()
    {
        $bhiPractice = $this->createPractice(false);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, true);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_if_patient_does_not_have_ccd_problems()
    {
        $bhiPractice = $this->createPractice(false);
        $bhiPatient  = $this->createPatient($bhiPractice->id, false, true);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_if_patient_is_not_enrolled()
    {
        $bhiPractice = $this->createPractice(false);
        $bhiPatient  = $this->createPatient($bhiPractice->id, false, true);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_if_practice_does_not_support_services()
    {
        $bhiPractice = $this->createPractice(false);
        $bhiPatient  = $this->createPatient($bhiPractice->id);

        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_retrieves_practices_that_require_consent()
    {
        $bhiPractice = $this->createPractice(true);
        AppConfig::create([
            'config_key'   => PracticesRequiringBhiConsent::PRACTICE_REQUIRES_BHI_CONSENT_NOVA_KEY,
            'config_value' => $bhiPractice->name, ]);

        $needConsent = PracticesRequiringBhiConsent::names();

        $this->assertTrue(in_array($bhiPractice->name, $needConsent));
    }

    private function createPatient(
        $practiceId,
        $enrolled = true,
        $hasBhiProblem = true,
        $hasBhiConsentNote = true,
        $consentedBeforeBhiDate = true
    ) {
        $patient = $this->createUser($practiceId, 'participant');

        $patient->patientInfo()
            ->update([
                'ccm_status' => $enrolled
                    ? Patient::ENROLLED
                    : Patient::PAUSED,
                'consent_date' => $consentedBeforeBhiDate
                    ? Carbon::parse(Patient::DATE_CONSENT_INCLUDES_BHI)->subWeek()
                    : Carbon::parse(Patient::DATE_CONSENT_INCLUDES_BHI),
            ]);

        if ($hasBhiConsentNote) {
            $now = Carbon::now();

            $patient->notes()
                ->create([
                    'author_id'    => factory(User::class)->create()->id,
                    'body'         => "Patient consented to receive BHI care on {$now->toDateTimeString()}",
                    'type'         => Patient::BHI_CONSENT_NOTE_TYPE,
                    'performed_at' => $now->toDateTimeString(),
                ]);
        }

        if ($hasBhiProblem) {
            $bhiProblem = CpmProblem::where('is_behavioral', '=', true)
                ->firstOrFail();

            $patient->ccdProblems()
                ->create([
                    'cpm_problem_id' => $bhiProblem->id,
                    'name'           => $bhiProblem->name,
                ]);
        }

        return $patient;
    }

    private function createPractice($bhi = false)
    {
        $practice = factory(Practice::class)->create([]);

        if ($bhi) {
            $practice->chargeableServices()
                ->attach(ChargeableService::whereCode('CPT 99484')->firstOrFail()->id);
        }

        return $practice;
    }
}
