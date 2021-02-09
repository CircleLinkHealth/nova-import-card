<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;

class PatientForcedChargeableServicesTest extends CustomerTestCase
{
    public function test_historical_records_are_created_for_deleted_permanent_forced_cs()
    {
        PatientForcedChargeableService::unguard();
        $forcedCs = PatientForcedChargeableService::create([
            'patient_user_id'       => $this->patient()->id,
            'action_type'           => PatientForcedChargeableService::FORCE_ACTION_TYPE,
            'chargeable_month'      => null,
            'chargeable_service_id' => $csId = ChargeableService::getChargeableServiceIdUsingCode(ChargeableService::CCM),
            'created_at'            => Carbon::now()->subMonths(6)->startOfMonth(),
        ]);

        $forcedCs->delete();

        $this->assertEquals(6,
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


        $forcedCs = PatientForcedChargeableService::create([
            'patient_user_id'       => $this->patient()->id,
            'action_type'           => PatientForcedChargeableService::BLOCK_ACTION_TYPE,
            'chargeable_month'      => null,
            'chargeable_service_id' => $csId,
            'created_at'            => Carbon::now()
        ]);
        $this->assertEquals(6,
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
}
