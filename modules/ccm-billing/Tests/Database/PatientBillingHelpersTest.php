<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

class PatientBillingHelpersTest extends CustomerTestCase
{
    use PracticeHelpers;
    use UserHelpers;

    protected $location;

    protected $practice;

    public function setUp(): void
    {
        parent::setUp();

        $this->practice = $this->setupPractice(true, true, true, true);
    }

    public function test_patient_is_bhi_helper_uses_new_summaries()
    {
        BillingCache::clearPatients();
        BillingCache::clearLocations();
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
        BillingCache::clearPatients();
        BillingCache::clearLocations();
        $patient = $this->setupPatient($this->practice, false, true);

        self::assertTrue(Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG));
        self::assertTrue($patient->isPcm());
        self::assertTrue(
            $patient->chargeableMonthlySummariesView()
                ->where('chargeable_service_code', ChargeableService::PCM)
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
