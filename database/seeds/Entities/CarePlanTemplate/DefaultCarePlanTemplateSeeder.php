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


        /*
         * Relate all CpmLifestyles and get their ui_sort from CarePlanItem
         */
        $cpmLifestyles = \App\Models\CPM\CpmLifestyle::all();
        $cpt->cpmLifestyles()->sync($cpmLifestyles);

        foreach ($cpmLifestyles as $lifestyle) {
            $cpi = \App\CarePlanItem::whereItemId($lifestyle->care_item_id)->first();
            $cpt->cpmLifestyles()->updateExistingPivot($lifestyle->id, ['ui_sort' => $cpi->ui_sort]);
        }


        /*
         * Relate all CpmMedicationGroups and get their ui_sort from CarePlanItem
         */
        $cpmMedGroup = \App\Models\CPM\CpmMedicationGroup::all();
        $cpt->cpmMedicationGroups()->sync($cpmMedGroup);

        foreach ($cpmMedGroup as $medGroup) {
            $cpi = \App\CarePlanItem::whereItemId($medGroup->care_item_id)->first();
            $cpt->cpmMedicationGroups()->updateExistingPivot($medGroup->id, ['ui_sort' => $cpi->ui_sort]);
        }


        /*
         * Relate all CpmMisc and get their ui_sort from CarePlanItem
         */
        $cpmMisc = \App\Models\CPM\CpmMisc::all();
        $cpt->cpmMiscs()->sync($cpmMisc);

        foreach ($cpmMisc as $misc) {
            $cpi = \App\CarePlanItem::whereItemId($misc->care_item_id)->first();
            $cpt->cpmMiscs()->updateExistingPivot($misc->id, ['ui_sort' => $cpi->ui_sort]);
        }


        /*
         * Relate all CpmProblems and get their ui_sort from CarePlanItem
         */
        $cpmProblems = \App\Models\CPM\CpmProblem::all();
        $cpt->cpmProblems()->sync($cpmProblems);

        foreach ($cpmProblems as $problem) {
            $cpi = \App\CarePlanItem::whereItemId($problem->care_item_id)->first();
            $cpt->cpmProblems()->updateExistingPivot($problem->id, ['ui_sort' => $cpi->ui_sort]);
        }


        /*
         * Relate all CpmSymptoms and get their ui_sort from CarePlanItem
         */
        $cpmSymptoms = \App\Models\CPM\CpmSymptom::all();
        $cpt->cpmSymptoms()->sync($cpmSymptoms);

        foreach ($cpmSymptoms as $problem) {
            $cpi = \App\CarePlanItem::whereItemId($problem->care_item_id)->first();
            $cpt->cpmSymptoms()->updateExistingPivot($problem->id, ['ui_sort' => $cpi->ui_sort]);
        }
    }
}