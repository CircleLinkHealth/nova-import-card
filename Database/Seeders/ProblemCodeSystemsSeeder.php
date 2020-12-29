<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */
namespace CircleLinkHealth\Customer\Database\Seeders;

use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;
use Illuminate\Database\Seeder;

class ProblemCodeSystemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        collect([
            'ICD-9',
            'ICD-10',
            'SNOMED CT',
        ])->each(function ($name) {
            ProblemCodeSystem::updateOrCreate([
                'name' => $name,
            ]);
        });
    }
}
