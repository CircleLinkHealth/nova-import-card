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
        //first update or create the default template
        $cpt = \App\CarePlanTemplate::updateOrCreate([
            'display_name' => \App\CarePlanTemplate::CLH_DEFAULT,
            'type' => \App\CarePlanTemplate::CLH_DEFAULT,
        ]);

        /*
         * Relate all CpmBiometrics and get their ui_sort from CarePlanItem
         */
        $cpmBiometrics = \App\Models\CPM\CpmBiometric::all();
        $cpt->cpmBiometrics()
            ->sync($cpmBiometrics);

        foreach ($cpmBiometrics as $biometric) {
            $cpi = \App\CarePlanItem::whereItemId($biometric->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            $cpt->cpmBiometrics()
                ->updateExistingPivot($biometric->id, [
                    'ui_sort' => $cpi->ui_sort,
                    'page' => 2,
                ]);
        }

        /*
         * Relate all CpmLifestyles and get their ui_sort from CarePlanItem
         */
        $cpmLifestyles = \App\Models\CPM\CpmLifestyle::all();
        $cpt->cpmLifestyles()->sync($cpmLifestyles);

        foreach ($cpmLifestyles as $lifestyle) {
            $cpi = \App\CarePlanItem::whereItemId($lifestyle->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            if (empty($cpi)) dd($lifestyle);

            $cpt->cpmLifestyles()->updateExistingPivot($lifestyle->id, [
                'ui_sort' => $cpi->ui_sort,
                'page' => 1,
            ]);
        }


        /*
         * Relate all CpmMedicationGroups and get their ui_sort from CarePlanItem
         */
        $cpmMedGroup = \App\Models\CPM\CpmMedicationGroup::all();
        $cpt->cpmMedicationGroups()->sync($cpmMedGroup);

        foreach ($cpmMedGroup as $medGroup) {
            $cpi = \App\CarePlanItem::whereItemId($medGroup->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            if (empty($cpi)) dd($medGroup);

            $cpt->cpmMedicationGroups()->updateExistingPivot($medGroup->id, [
                'ui_sort' => $cpi->ui_sort,
                'page' => 1,
            ]);
        }


        /*
         * Relate all CpmMisc and get their ui_sort from CarePlanItem
         */
        $cpmMisc = \App\Models\CPM\CpmMisc::all();
        $cpt->cpmMiscs()->sync($cpmMisc);

        foreach ($cpmMisc as $misc) {
            $cpi = \App\CarePlanItem::whereItemId($misc->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            if (empty($cpi)) dd($misc);

            if ($misc->name == \App\Models\CPM\CpmMisc::OTHER_CONDITIONS
                || $misc->name == \App\Models\CPM\CpmMisc::MEDICATION_LIST)
            {
                $cpt->cpmMiscs()->updateExistingPivot($misc->id, [
                    'ui_sort' => $cpi->ui_sort,
                    'page' => 1,
                ]);
            }
            elseif ($misc->name == \App\Models\CPM\CpmMisc::TRACK_CARE_TRANSITIONS)
            {
                $cpt->cpmMiscs()->updateExistingPivot($misc->id, [
                    'ui_sort' => $cpi->ui_sort,
                    'page' => 2,
                ]);
            } 
            else 
            {
                $cpt->cpmMiscs()->updateExistingPivot($misc->id, [
                    'ui_sort' => $cpi->ui_sort,
                    'page' => 3,
                ]);
            }
            
        }


        /*
         * Relate all CpmProblems and get their ui_sort from CarePlanItem
         */
        $cpmProblems = \App\Models\CPM\CpmProblem::all();
        $cpt->cpmProblems()->sync($cpmProblems);

        foreach ($cpmProblems as $problem) {
            $cpi = \App\CarePlanItem::whereItemId($problem->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            if (empty($cpi)) dd($problem);

            $cpt->cpmProblems()->updateExistingPivot($problem->id, [
                'ui_sort' => $cpi->ui_sort,
                'page' => 1,
            ]);
        }


        /*
         * Relate all CpmSymptoms and get their ui_sort from CarePlanItem
         */
        $cpmSymptoms = \App\Models\CPM\CpmSymptom::all();
        $cpt->cpmSymptoms()->sync($cpmSymptoms);

        foreach ($cpmSymptoms as $problem) {
            $cpi = \App\CarePlanItem::whereItemId($problem->care_item_id)->wherePlanId(DEFAULT_LEGACY_CARE_PLAN_ID)->first();

            if (empty($cpi)) dd($problem);

            $cpt->cpmSymptoms()->updateExistingPivot($problem->id, [
                'ui_sort' => $cpi->ui_sort,
                'page' => 3,
            ]);
        }
    }
}