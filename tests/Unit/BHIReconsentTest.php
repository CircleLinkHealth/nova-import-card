<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Call;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Customer\SetupPracticeBillingData;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\AppConfig\PracticesRequiringSpecialBhiConsent;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Tests\CustomerTestCase;

class BHIReconsentTest extends CustomerTestCase
{
    use UserHelpers;

    public function test_it_hides_flag_past_tomorrow_if_patient_has_more_calls_today_and_not_now_was_clicked()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, true);

        //Create 2 calls for today
        $c1 = $this->createCall($this->careCoach(), $bhiPatient, Carbon::now());
        $c2 = $this->createCall($this->careCoach(), $bhiPatient, Carbon::now());

        //Add billing provider
        $billing = CarePerson::create(
            [
                'alert'          => true,
                'user_id'        => $bhiPatient->id,
                'member_user_id' => $this->provider()->id,
                'type'           => CarePerson::BILLING_PROVIDER,
            ]
        );

        $this->assertTrue($bhiPatient->isLegacyBhiEligible());
        $this->assertTrue($this->careCoach()->shouldShowBhiFlagFor($bhiPatient));

        //store not now response as a nurse
        $response = $this->actingAs($this->careCoach())->call('POST', route('legacy-bhi.store', [$bhiPatient->program_id, $bhiPatient->id]), [
            //"Not Now" response
            'decision' => 2,
        ])->assertStatus(302);

        $cacheKey = $this->careCoach()->getLegacyBhiNursePatientCacheKey($bhiPatient->id);
        $this->assertTrue(\Cache::has($cacheKey));

        $timeTillShowAgain = \Cache::get($cacheKey);
        $this->assertTrue(Carbon::now()->addMinutes($timeTillShowAgain)->isAfter(Carbon::tomorrow()->startOfDay()));
    }

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
        AppConfig::set(PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, $bhiPractice->name);
        
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
        AppConfig::set(PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, $bhiPractice->name);

        ProcessSinglePatientMonthlyServices::dispatch($bhiPatient->id);

        $this->assertTrue($bhiPatient->isBhi());
    }

    public function test_it_is_not_bhi_for_after_cutoff_consent_date_and_practice_requires_consent()
    {
        $bhiPractice = $this->createPractice(true);
        $bhiPatient  = $this->createPatient($bhiPractice->id, true, true, false, false);
        AppConfig::set(PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, $bhiPractice->name);
        
        //todo next iteration: is this a realistic scenario to happen say in the middle of the month? I think not, still try and cleanup either code or test
        $bhiPatient->chargeableMonthlySummaries()->delete();
        ProcessSinglePatientMonthlyServices::dispatch($bhiPatient->id);
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
        AppConfig::set(PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, $bhiPractice->name);

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
    
        $bhiPatient->chargeableMonthlySummaries()->delete();
        ProcessSinglePatientMonthlyServices::dispatch($bhiPatient->id);
        $this->assertFalse($bhiPatient->isBhi());
    }

    public function test_it_retrieves_practices_that_require_consent()
    {
        $bhiPractice = $this->createPractice(true);
        AppConfig::set(PracticesRequiringSpecialBhiConsent::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, $bhiPractice->name);

        $needConsent = PracticesRequiringSpecialBhiConsent::names();

        $this->assertTrue(in_array($bhiPractice->name, $needConsent));
    }

    private function createCall(User $nurse, User $patient, Carbon $date)
    {
        return Call::create([
            'type'    => SchedulerService::CALL_TYPE,
            'service' => 'phone',
            'status'  => 'scheduled',

            'attempt_note' => '',

            'scheduler' => $nurse->id,
            'is_manual' => 1,

            'inbound_phone_number' => '',

            'outbound_phone_number' => '',

            'inbound_cpm_id'  => $patient->id,
            'outbound_cpm_id' => $nurse->id,

            'call_time'  => 0,
            'created_at' => Carbon::now()->toDateTimeString(),

            //make sure we are sending the date correctly formatted
            'scheduled_date' => $date->format('Y-m-d'),
            'window_start'   => '09:00',
            'window_end'     => '17:00',

            'is_cpm_outbound' => true,
        ]);
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

        $patient->patientInfo->fresh();

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
                    'is_monitored'   => true,
                ]);
        }
        
        ProcessSinglePatientMonthlyServices::dispatch($patient->id);

        return $patient;
    }

    private function createPractice($bhi = false)
    {
        if ($bhi) {
            $this->practice()->chargeableServices()
                ->sync([ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI)]);
        } else {
            $this->practice()->chargeableServices()
                ->sync([ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM)]);
        }
        SetupPracticeBillingData::sync($this->practice()->id);

        return $this->practice();
    }
}
