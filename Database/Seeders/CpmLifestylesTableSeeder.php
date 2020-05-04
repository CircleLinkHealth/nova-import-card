<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmLifestyle;
use Illuminate\Database\Seeder;

class CpmLifestylesTableSeeder extends Seeder
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
            'Diabetic Diet',
            'Exercise',
            'Healthy Diet',
            'Low Salt Diet',
        ] as $lifestyle) {
            $l = CpmLifestyle::updateOrCreate(['name' => $lifestyle]);

            $carePlanTemplates->each(function (CarePlanTemplate $cpt) use ($l) {
                $cpt->cpmLifestyles()->attach($l);
            });
        }
    }
}
