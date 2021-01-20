<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use CircleLinkHealth\Customer\Entities\ChargeableService;

class SeedChargeableServices
{
    public static function execute()
    {
        ChargeableService::updateOrCreate([
            'code' => ChargeableService::CCM,
        ], [
            'order'        => 1,
            'is_enabled'   => true,
            'description'  => 'CCM Services over 20 mins (1 month)',
            'amount'       => null,
            'display_name' => 'CCM',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::BHI,
        ], [
            'order'        => 2,
            'is_enabled'   => true,
            'description'  => 'Behavioural Health Services over 20 mins (1 month)',
            'amount'       => null,
            'display_name' => 'BHI',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::CCM_PLUS_40,
        ], [
            'order'        => 3,
            'is_enabled'   => true,
            'description'  => 'CCM services over 40 mins (1 month)',
            'amount'       => null,
            'display_name' => 'CCM40',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::CCM_PLUS_60,
        ], [
            'order'        => 4,
            'is_enabled'   => true,
            'description'  => 'CCM services over 60 mins (1 month)',
            'amount'       => null,
            'display_name' => 'CCM60',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::GENERAL_CARE_MANAGEMENT,
        ], [
            'order'        => 5,
            'is_enabled'   => true,
            'description'  => 'FQHC / RHC General Care Management (1 month)',
            'amount'       => null,
            'display_name' => 'CCM (RHC/FQHC)',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::SOFTWARE_ONLY,
        ], [
            'order'       => 6,
            'is_enabled'  => true,
            'description' => 'Customer uses their own Care Center',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::AWV_INITIAL,
        ], [
            'order'        => 7,
            'is_enabled'   => true,
            'description'  => 'Initial Annual Wellness Visit',
            'amount'       => null,
            'display_name' => 'AWV1',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::AWV_SUBSEQUENT,
        ], [
            'order'        => 8,
            'is_enabled'   => true,
            'description'  => 'Subsequent Annual Wellness Visit',
            'amount'       => null,
            'display_name' => 'AWV2+',
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99487',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Complex CCM over 60 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'CPT 99489',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Complex CCM additional 30 mins (1 month)',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => 'G0506',
        ], [
            'order'       => null,
            'is_enabled'  => false,
            'description' => 'Enrollment in office & Care Planning by Provider',
            'amount'      => null,
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::PCM,
        ], [
            'order'        => 9,
            'is_enabled'   => true,
            'description'  => 'PCM: Principal Care Management over 30 Minutes (1 month)',
            'amount'       => null,
            'display_name' => 'PCM',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::RPM,
        ], [
            'order'        => 10,
            'is_enabled'   => true,
            'description'  => 'Remote Patient Monitoring',
            'amount'       => null,
            'display_name' => 'RPM',
        ]);

        ChargeableService::updateOrCreate([
            'code' => ChargeableService::RPM40,
        ], [
            'order'        => 11,
            'is_enabled'   => true,
            'description'  => 'Remote Patient Monitoring over 40 minutes',
            'amount'       => null,
            'display_name' => 'RPM40',
        ]);
        ChargeableService::updateOrCreate([
            'code' => ChargeableService::RPM60,
        ], [
            'order'        => 12,
            'is_enabled'   => true,
            'description'  => 'Remote Patient Monitoring over 60 minutes',
            'amount'       => null,
            'display_name' => 'RPM60',
        ]);
    }
}
