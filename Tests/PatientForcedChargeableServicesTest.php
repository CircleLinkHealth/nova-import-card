<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Caches\BillingCache;
use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\ValueObjects\ForceAttachInputDTO;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;

class PatientForcedChargeableServicesTest extends CustomerTestCase
{
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

    public function test_system_accounts_for_forced_cs_when_determining_if_patient_is_of_service_code()
    {
        //test with Billing revamp on
        BillingCache::setBillingRevampIsEnabled(true);

//        $patient = $this->setupPatient()

        //test with Billing revamp off
    }
}
