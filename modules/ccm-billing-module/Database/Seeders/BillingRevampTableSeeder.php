<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Database\Seeder;

class BillingRevampTableSeeder extends Seeder
{
    const VARIANT_OFF = '"off"';
    const VARIANT_ON  = '"on"';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FeatureFlag::insert([
            [
                'key'      => BillingConstants::BILLING_REVAMP_FLAG,
                'variants' => self::VARIANT_ON,
            ],
            [
                'key'      => BillingConstants::LOCATION_PROBLEM_SERVICES_FLAG,
                'variants' => self::VARIANT_OFF,
            ],
            [
                'key'      => BillingConstants::AWV_BILLING_FLAG,
                'variants' => self::VARIANT_OFF,
            ],
        ]);
    }
}
