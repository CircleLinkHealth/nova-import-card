<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use CircleLinkHealth\SharedModels\Entities\CpmMisc;
use Illuminate\Database\Seeder;

class CpmMiscsTableSeeder extends Seeder
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
            'Allergies',
            'Appointments',
            'Full Conditions List',
            'Medication List',
            'Other',
            'Social Services',
        ] as $misc) {
            $m = CpmMisc::updateOrCreate(
                [
                    'name' => $misc,
                ]
            );
            $carePlanTemplates->each(function (CarePlanTemplate $cpt) use ($m) {
                $cpt->cpmMiscs()->attach($m);
            });
        }
    }
}
