<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Entities\SaasAccount;
use Illuminate\Database\Seeder;

class SaasAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SaasAccount::updateOrCreate(
            [
                'name' => 'CircleLink Health',
            ]
        );
    }
}
