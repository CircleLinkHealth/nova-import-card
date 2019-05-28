<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        Model::unguard();

        $this->call(AppConfigTableSeeder::class);
        $this->call(CpmProblemsTableSeeder::class);
        $this->call(AddNewDefaultCarePlanTemplate::class);
        $this->call(RequiredRolesPermissionsSeeder::class);
        $this->call(ChargeableServiceSeeder::class);
        $this->call(ProblemCodeSystemsSeeder::class);
        $this->call(SaasAccountsSeeder::class);
        $this->call(SnomedToIcd9TestMapTableSeeder::class);
    }
}
