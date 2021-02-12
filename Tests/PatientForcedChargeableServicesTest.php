<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Events\PatientConsentedToService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\PracticeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Facades\Cache;

class PatientForcedChargeableServicesTest extends CustomerTestCase
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
    
    public function test_historical_records_are_created_for_deleted_permanent_forced_cs()
    {
        PatientForcedChargeableService::unguard();
        PatientForcedChargeableService::create([
            'patient_user_id'       => $this->patient()->id,
            'action_type'           => PatientForcedChargeableService::FORCE_ACTION_TYPE,
            'chargeable_month'      => null,
            'chargeable_service_id' => $csId = ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
            'created_at'            => Carbon::now()->subMonths(6)->startOfMonth(),
        ]);

        ForcePatientChargeableService::execute(
            (new ForceAttachInputDTO())
                ->setPatientUserId($this->patient()->id)
                ->setChargeableServiceId($csId)
                ->setActionType(PatientForcedChargeableService::FORCE_ACTION_TYPE)
                ->setIsDetaching(true)
        );

        $this->assertEquals(
            6,
            PatientForcedChargeableService::where('chargeable_service_id', $csId)
                ->where('patient_user_id', $this->patient()->id)
                ->where('action_type', PatientForcedChargeableService::FORCE_ACTION_TYPE)
                ->count()
        );
    }

    public function test_historical_records_are_created_for_deleted_permanent_forced_cs_upon_inserting_opposite_permanent_forced_cs()
    {
        PatientForcedChargeableService::unguard();
        PatientForcedChargeableService::create([
            'patient_user_id'       => $this->patient()->id,
            'action_type'           => PatientForcedChargeableService::FORCE_ACTION_TYPE,
            'chargeable_month'      => null,
            'chargeable_service_id' => $csId = ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
            'created_at'            => Carbon::now()->subMonths(6)->startOfMonth(),
        ]);

        ForcePatientChargeableService::execute(
            (new ForceAttachInputDTO())
                ->setPatientUserId($this->patient()->id)
                ->setChargeableServiceId($csId)
                ->setActionType(PatientForcedChargeableService::BLOCK_ACTION_TYPE)
        );

        $this->assertEquals(
            6,
            PatientForcedChargeableService::where('chargeable_service_id', $csId)
                ->where('patient_user_id', $this->patient()->id)
                ->where('action_type', PatientForcedChargeableService::FORCE_ACTION_TYPE)
                ->count()
        );
        $this->assertEquals(1, PatientForcedChargeableService::where('chargeable_service_id', $csId)
            ->where('patient_user_id', $this->patient()->id)
            ->where('action_type', PatientForcedChargeableService::BLOCK_ACTION_TYPE)
            ->count());
    }

    public function test_system_accounts_for_forced_and_blocked_cs_when_determining_if_patient_is_of_service_code()
    {
        //test with Billing revamp on
        BillingCache::setBillingRevampIsEnabled(true);
    
        BillingCache::clearPatients();
        BillingCache::clearLocations();
        $bhiPatient = $this->setupPatient($this->practice, true);
    
        $bhiPatient->notes()->create([
            'type'      => Patient::BHI_CONSENT_NOTE_TYPE,
            'author_id' => $this->careCoach()->id,
        ]);
    
        event(new PatientConsentedToService($bhiPatient->id, $bhiCode = ChargeableService::BHI));
        $bhiCodeId = ChargeableService::getChargeableServiceIdUsingCode($bhiCode);
    
        BillingCache::clearPatients();
        self::assertTrue(BillingCache::billingRevampIsEnabled());
        self::assertTrue($bhiPatient->isBhi());
        self::assertTrue(
            $bhiPatient->chargeableMonthlySummaries()
                ->where('chargeable_service_id', $bhiCodeId)
                ->where('chargeable_month', $month = Carbon::now()->startOfMonth())
                ->exists()
        );
        Cache::forget("user:$bhiPatient->id:is_bhi");
        ForcePatientChargeableService::execute(
            (new ForceAttachInputDTO())->setChargeableServiceId($bhiCodeId)
            ->setPatientUserId($bhiPatient->id)
            ->setActionType(PatientForcedChargeableService::BLOCK_ACTION_TYPE)
        );
    
        self::assertFalse($bhiPatient->isBhi());

        //test with Billing revamp off
    }
}
