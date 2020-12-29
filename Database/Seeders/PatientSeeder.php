<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace CircleLinkHealth\Customer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Validation\ValidationException;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        if ( ! isProductionEnv() && isCpm()) {
            try {
                (new \App\Testing\TestPatients())->create();
            } catch (ValidationException $e) {
                dd($e->validator->errors()->all());
            }
            $this->command->info('Test patients seeded');
        }
    }
}
