<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Database\Seeders;

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
