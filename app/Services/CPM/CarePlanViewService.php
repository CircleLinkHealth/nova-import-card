<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:52 PM
 */

namespace App\Services\CPM;


use App\PatientCarePlan as CarePlan;
use App\CarePlanTemplate;

class CarePlanViewService
{
    public function carePlanFirstPage(CarePlan $carePlan)
    {
        if (empty($carePlan)) return false;

        $cptId = $carePlan->care_plan_template_id;

        return CarePlanTemplate::find($cptId)
            ->load([
                'cpmLifestyles' => function ($query) {
                    $query->with('cpmInstructions');
                    $query->orderBy('pivot_ui_sort');
                },
                'cpmMedicationGroups' => function ($query) {
                    $query->with('cpmInstructions');
                    $query->orderBy('pivot_ui_sort');
                },
                'cpmProblems' => function ($query) {
                    $query->with('cpmInstructions');
                    $query->orderBy('pivot_ui_sort');
                },
            ]);
    }

}