<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Seeder;

class ChargeableServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \CircleLinkHealth\CcmBilling\Domain\Customer\SeedChargeableServices::execute();
    }
}
