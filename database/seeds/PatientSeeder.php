<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if (! isProductionEnv()) {
            (new \App\Testing\TestPatients())->create();
            $this->command->info('Test patients seeded');
        }
    }
}
