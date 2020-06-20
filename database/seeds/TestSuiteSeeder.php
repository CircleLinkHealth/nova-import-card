<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\Database\Seeders\RequiredRolesPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class TestSuiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        //Order is important here. Do not re-arrange unless you know what you are doing!
        $this->call(SaasAccountsSeeder::class);
        $this->call(CpmProblemsTableSeeder::class);
        $this->call(AddNewDefaultCarePlanTemplate::class);
        $this->call(MedicationGroupsTableSeeder::class);
        $this->call(CpmLifestylesTableSeeder::class);
        $this->call(CpmBiometricsTableSeeder::class);
        $this->call(CpmSymptomsTableSeeder::class);
        $this->call(CpmMiscsTableSeeder::class);
        $this->call(CpmDefaultInstructionSeeder::class);
        $this->call(RequiredRolesPermissionsSeeder::class);
        $this->call(ChargeableServiceSeeder::class);
        $this->call(ProblemCodeSystemsSeeder::class);
        $this->call(PracticeTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(PatientSeeder::class);
        $this->call(EnrolleesSeeder::class);
        $this->call(PrepareDataForReEnrollmentTestSeeder::class);
        $this->call(CareAmbassadorDefaultScriptsSeeder::class);
    }
}
