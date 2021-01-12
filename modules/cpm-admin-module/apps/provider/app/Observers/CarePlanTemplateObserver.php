<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\SharedModels\Entities\CarePlanTemplate;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CarePlanTemplateObserver
{
    /**
     * Listen to the User created event.
     *
     * @param \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate $carePlanTemplate
     */
    public function created(CarePlanTemplate $newCPT)
    {
        $this->applyDefaults($newCPT);
    }

    private function applyDefaults(CarePlanTemplate $newCPT)
    {
        $defaultCPTExists = true;
        try {
            $defaultCPT = getDefaultCarePlanTemplate();
        } catch (ModelNotFoundException $e) {
            $defaultCPTExists = false;
        }

        if ($defaultCPTExists) {
            $biometrics = $defaultCPT->cpmBiometrics->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmBiometrics()->attach($array['cpm_biometric_id'], [
                    'care_plan_template_id' => $newCPT->id,
                    'cpm_biometric_id'      => $array['cpm_biometric_id'],
                    'has_instruction'       => $array['has_instruction'],
                    'cpm_instruction_id'    => $array['cpm_instruction_id'],
                    'page'                  => $array['page'],
                    'ui_sort'               => $array['ui_sort'],
                ]);
            });

            $lifestyles = $defaultCPT->cpmLifestyles->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmLifestyles()->attach($array['cpm_lifestyle_id'], [
                    'care_plan_template_id' => $newCPT->id,
                    'cpm_lifestyle_id'      => $array['cpm_lifestyle_id'],
                    'has_instruction'       => $array['has_instruction'],
                    'cpm_instruction_id'    => $array['cpm_instruction_id'],
                    'page'                  => $array['page'],
                    'ui_sort'               => $array['ui_sort'],
                ]);
            });

            $medGroups = $defaultCPT->cpmMedicationGroups->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmMedicationGroups()->attach($array['cpm_medication_group_id'], [
                    'care_plan_template_id'   => $newCPT->id,
                    'cpm_medication_group_id' => $array['cpm_medication_group_id'],
                    'has_instruction'         => $array['has_instruction'],
                    'cpm_instruction_id'      => $array['cpm_instruction_id'],
                    'page'                    => $array['page'],
                    'ui_sort'                 => $array['ui_sort'],
                ]);
            });

            $miscs = $defaultCPT->cpmMiscs->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmMiscs()->attach($array['cpm_misc_id'], [
                    'care_plan_template_id' => $newCPT->id,
                    'cpm_misc_id'           => $array['cpm_misc_id'],
                    'has_instruction'       => $array['has_instruction'],
                    'cpm_instruction_id'    => $array['cpm_instruction_id'],
                    'page'                  => $array['page'],
                    'ui_sort'               => $array['ui_sort'],
                ]);
            });

            $problems = $defaultCPT->cpmProblems->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmProblems()->attach($array['cpm_problem_id'], [
                    'care_plan_template_id' => $newCPT->id,
                    'cpm_problem_id'        => $array['cpm_problem_id'],
                    'has_instruction'       => $array['has_instruction'],
                    'cpm_instruction_id'    => $array['cpm_instruction_id'],
                    'page'                  => $array['page'],
                    'ui_sort'               => $array['ui_sort'],
                ]);
            });

            $symptoms = $defaultCPT->cpmSymptoms->map(function ($row) use ($newCPT) {
                $array = $row->pivot->toArray();
                $newCPT->cpmSymptoms()->attach($array['cpm_symptom_id'], [
                    'care_plan_template_id' => $newCPT->id,
                    'cpm_symptom_id'        => $array['cpm_symptom_id'],
                    'has_instruction'       => $array['has_instruction'],
                    'cpm_instruction_id'    => $array['cpm_instruction_id'],
                    'page'                  => $array['page'],
                    'ui_sort'               => $array['ui_sort'],
                ]);
            });
        }
    }
}
