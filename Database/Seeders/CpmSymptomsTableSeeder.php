<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmSymptom;
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
        $carePlanTemplates = CarePlanTemplate::get();
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
            $s = CpmSymptom::updateOrCreate(['name' => $symptom]);
            $carePlanTemplates->each(function (CarePlanTemplate $cpt) use ($s) {
                $cpt->cpmSymptoms()->attach($s);
            });
        }
    }
}
