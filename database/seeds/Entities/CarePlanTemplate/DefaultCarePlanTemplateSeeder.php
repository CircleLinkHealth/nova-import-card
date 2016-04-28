<?php

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 10:26 AM
 */
class DefaultCarePlanTemplateSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        $cpt = \App\CarePlanTemplate::updateOrCreate([
            'display_name' => \App\CarePlanTemplate::CLH_DEFAULT,
            'type' => \App\CarePlanTemplate::CLH_DEFAULT,
        ]);
        
        $cpmProblems = \App\Models\CPM\CpmProblem::all();
        $cpt->cpmProblems()->sync($cpmProblems);

        foreach ($cpmProblems as $problem) {
            $cpi = \App\CarePlanItem::whereItemId($problem->care_item_id)->first();
            $cpt->cpmProblems()->updateExistingPivot($problem->id, ['ui_sort' => $cpi->ui_sort]);
        }
    }
}