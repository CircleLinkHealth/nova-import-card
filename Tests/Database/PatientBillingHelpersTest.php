<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\Jobs\ProcessSinglePatientMonthlyServices;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Tests\CustomerTestCase;

class PatientBillingHelpersTest extends CustomerTestCase
{
    use UserHelpers;
    use PracticeHelpers;
    
    protected $location;
    
    protected $practice;
    
    public function setUp(): void
    {
        parent::setUp();
        
        $this->practice = $this->setupPractice(true, true, true, true);
    }
    
    
    public function test_patient_bhi_time_helper_uses_new_summaries()
    {
    }

    public function test_patient_ccm_time_helper_uses_new_summaries()
    {
    }

    public function test_patient_is_bhi_helper_uses_new_summaries()
    {
        $bhiPatient = $this->setupPatient($this->practice, true);
        
        $bhiPatient->notes()->create([
            'type'      => Patient::BHI_CONSENT_NOTE_TYPE,
            'author_id' => $this->careCoach()->id,
        ]);
        
        event(new PatientConsentedToService($bhiPatient->id, $bhiCode = ChargeableService::BHI));
        
        self::assertTrue(Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG));
        self::assertTrue($bhiPatient->isBhi());
        self::assertTrue(
            $bhiPatient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', $bhiCode)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
        ->exists()
        );
        self::assertFalse(
            $bhiPatient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', ChargeableService::PCM)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
    }

    public function test_patient_is_pcm_helper_uses_new_summaries()
    {
        $patient = $this->setupPatient($this->practice, false, true);
    
        event(new PatientConsentedToService($patient->id, $pcmCode = ChargeableService::PCM));
    
        self::assertTrue(Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG));
        self::assertTrue($patient->isBhi());
        self::assertTrue(
            $patient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', $pcmCode)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
        self::assertFalse(
            $patient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', ChargeableService::BHI)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
        self::assertFalse(
            $patient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', ChargeableService::CCM)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
    }
}
