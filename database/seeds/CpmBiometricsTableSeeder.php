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
        /*
         * Disabled because it was throwing exception:
            In Connection.php line 664:
              [Illuminate\Database\QueryException (23000)]
              SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1-1'
               for key 'cpt_id_cpm_bmtrc_id_unique' (SQL: insert into `care_plan_template
              s_cpm_biometrics` (`care_plan_template_id`, `cpm_biometric_id`, `created_at
              `, `updated_at`) values (1, 1, 2020-02-29 03:31:33, 2020-02-29 03:31:33))

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
                $cpt->cpmBiometrics()->attach($b);
            });
        }
        */
    }
}
