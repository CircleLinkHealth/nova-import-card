<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Database;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;

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
        $bhiPatient = $this->setupPatient($this->practice, true);

        $bhiPatient->notes()->create([
            'type'      => Patient::BHI_CONSENT_NOTE_TYPE,
            'author_id' => $this->careCoach()->id,
        ]);

        event(new PatientConsentedToService($bhiPatient->id, $bhiCode = ChargeableService::BHI));

        $bhiCodeId = ChargeableService::getChargeableServiceIdUsingCode($bhiCode);

        self::assertTrue($bhiPatient->isBhi());
        self::assertTrue(
            $bhiPatient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', $bhiCodeId)
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
        self::assertFalse(
            $bhiPatient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::PCM))
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
    }

    public function test_patient_is_pcm_helper_uses_new_summaries()
    {
        $patient = $this->setupPatient($this->practice, false, true);

        self::assertTrue($patient->isPcm());
        self::assertTrue(
            $patient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::PCM))
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
        self::assertFalse(
            $patient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::BHI))
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
        self::assertFalse(
            $patient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM))
                ->where('chargeable_month', Carbon::now()->startOfMonth())
                ->exists()
        );
    }
}
