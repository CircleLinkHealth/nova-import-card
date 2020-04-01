<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmBiometric;
use Illuminate\Database\Seeder;

class CpmBiometricsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $carePlanTemplates = CarePlanTemplate::get();
        foreach (
            [
                ['Weight', 0, 'lbs'],
                ['Blood Pressure', 1, 'mm Hg'],
                ['Blood Sugar', 2, 'mg/dL'],
                ['Smoking (# per day)', 3, '# per day'],
            ] as $biometric
        ) {
            $b = CpmBiometric::updateOrCreate(
                [
                    'name' => $biometric[0],
                    'type' => $biometric[1],
                    'unit' => $biometric[2],
                ]
            );

            $carePlanTemplates->each(function (CarePlanTemplate $cpt) use ($b) {
                $cpt->cpmBiometrics()->syncWithoutDetaching($b);
            });
        }
    }
}
