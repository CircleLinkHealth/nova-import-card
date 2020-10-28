<?php

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class BillingRevampTableSeeder extends Seeder
{
    const VARIANT_ON = 'on';
    const VARIANT_OFF = 'off';
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FeatureFlag::insert([
            [
                'key' => BillingConstants::BILLING_REVAMP_FLAG,
                'variants' => self::VARIANT_ON
            ],
            [
                'key' => BillingConstants::LOCATION_PROBLEM_SERVICES_FLAG,
                'variants' => self::VARIANT_OFF
            ],
            [
                'key' => BillingConstants::AWV_BILLING_FLAG,
                'variants' => self::VARIANT_OFF
            ]
        ]);
    }
}
