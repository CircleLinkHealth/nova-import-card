<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Models\CPM\CpmSymptom;
use Illuminate\Database\Seeder;

class CpmSymptomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            'Anxiety',
            'Chest pain/tightness',
            'Coughing/wheezing',
            'Fatigue',
            'Feeling down/sleep changes',
            'Pain',
            'Palpitations',
            'Shortness of breath',
            'Sweating',
            'Swelling in legs/feet',
            'Weakness/dizziness',
        ] as $symptom) {
            CpmSymptom::updateOrCreate(['name' => $symptom]);
        }
    }
}
