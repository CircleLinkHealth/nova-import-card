<?php

use App\ProblemCodeSystem;
use function Aws\map;
use Illuminate\Database\Seeder;

class ProblemCodeSystemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        collect([
            'ICD-9',
            'ICD-10',
            'SNOMED CT',
        ])->each(function ($name) {
            ProblemCodeSystem::create([
                'name' => $name,
            ]);
        });
    }
}
