<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\SaasAccount;
use Illuminate\Database\Seeder;

class SaasAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        SaasAccount::create(
            [
                'name' => 'CircleLink Health',
            ]
        );
    }
}
