<?php

namespace App\CLH\CCD\Importer\StorageStrategies\Problems;


use App\CLH\CCD\Importer\StorageStrategies\BaseStorageStrategy;
use App\CLH\Contracts\CCD\StorageStrategy;
use App\Models\CPM\CpmProblem;


class ProblemsToMonitor extends BaseStorageStrategy implements StorageStrategy
{
    public function import($cpmProblemIds = [])
    {
        if (empty($cpmProblemIds)) return;

        $cpmProblems = CpmProblem::findMany($cpmProblemIds);

        foreach ($cpmProblems as $cpmProblem) {
            $instructions = $cpmProblem->cpmInstructions()->get();

            $args = [];

            if (!$instructions->isEmpty()) {
                $args['cpm_instruction_id'] = $instructions[0]->id;
            }

            $this->user->cpmProblems()->attach($cpmProblem->id, $args);

            $biometricsToActivate = $cpmProblem
                ->cpmBiometricsToBeActivated()
                ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
                ->pluck('cpm_biometric_id')
                ->all();

            $lifestylesToActivate = $cpmProblem
                ->cpmLifestylesToBeActivated()
                ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
                ->pluck('cpm_lifestyle_id')
                ->all();

            $medsToActivate = $cpmProblem
                ->cpmMedicationGroupsToBeActivated()
                ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
                ->pluck('cpm_medication_group_id')
                ->all();

            $symptomsToActivate = $cpmProblem
                ->cpmSymptomsToBeActivated()
                ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
                ->pluck('cpm_symptom_id')
                ->all();

            if ($biometricsToActivate) $this->user->cpmBiometrics()->sync($biometricsToActivate, false);

            if ($lifestylesToActivate) $this->user->cpmLifestyles()->sync($lifestylesToActivate, false);

            if ($medsToActivate) $this->user->cpmMedicationGroups()->sync($medsToActivate, false);

            if ($symptomsToActivate) $this->user->cpmSymptoms()->sync($symptomsToActivate, false);
        }

    }
}