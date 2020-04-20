<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategies;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\StorageStrategy;

class ProblemsToMonitor extends BaseStorageStrategy implements StorageStrategy
{
    public function detach($cpmProblemIds = [])
    {
        if (empty($cpmProblemIds)) {
            return;
        }

        $cpmProblems = $this->user->carePlan->carePlanTemplate->cpmProblems->whereIn('id', $cpmProblemIds);

        foreach ($cpmProblems as $cpmProblem) {
            $this->user->cpmProblems()->detach($cpmProblem->id);

            $problemData = $this->getProblemRelationships($cpmProblem);

            if ($problemData['biometricsToActivate']) {
                $this->user->cpmBiometrics()->detach($problemData['biometricsToActivate']);
            }

            if ($problemData['lifestylesToActivate']) {
                $this->user->cpmLifestyles()->detach($problemData['lifestylesToActivate']);
            }

            if ($problemData['medsToActivate']) {
                $this->user->cpmMedicationGroups()->detach($problemData['medsToActivate']);
            }

            if ($problemData['symptomsToActivate']) {
                $this->user->cpmSymptoms()->detach($problemData['symptomsToActivate']);
            }
        }
    }

    public function getProblemRelationships($cpmProblem)
    {
        $problem['biometricsToActivate'] = $cpmProblem
            ->cpmBiometricsToBeActivated()
            ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
            ->pluck('cpm_biometric_id')
            ->all();

        $problem['lifestylesToActivate'] = $cpmProblem
            ->cpmLifestylesToBeActivated()
            ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
            ->pluck('cpm_lifestyle_id')
            ->all();

        $problem['medsToActivate'] = $cpmProblem
            ->cpmMedicationGroupsToBeActivated()
            ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
            ->pluck('cpm_medication_group_id')
            ->all();

        $problem['symptomsToActivate'] = $cpmProblem
            ->cpmSymptomsToBeActivated()
            ->wherePivot('care_plan_template_id', $this->carePlanTemplateId)
            ->pluck('cpm_symptom_id')
            ->all();

        return $problem;
    }

    public function import($cpmProblemIds = [])
    {
        if (empty($cpmProblemIds)) {
            return;
        }

        $cpmProblems = $this->user->carePlan->carePlanTemplate->cpmProblems->whereIn('id', $cpmProblemIds);

        foreach ($cpmProblems as $cpmProblem) {
            $instructionsId = $cpmProblem->pivot->cpm_instruction_id;

            $args = [];

            if ($instructionsId) {
                $args['cpm_instruction_id'] = $instructionsId;
            }

            $this->user->cpmProblems()->attach($cpmProblem->id, $args);

            $problemData = $this->getProblemRelationships($cpmProblem);

            if ($problemData['biometricsToActivate']) {
                $this->user->cpmBiometrics()->sync($problemData['biometricsToActivate'], false);
            }

            if ($problemData['lifestylesToActivate']) {
                $this->user->cpmLifestyles()->sync($problemData['lifestylesToActivate'], false);
            }

            if ($problemData['medsToActivate']) {
                $this->user->cpmMedicationGroups()->sync($problemData['medsToActivate'], false);
            }

            if ($problemData['symptomsToActivate']) {
                $this->user->cpmSymptoms()->sync($problemData['symptomsToActivate'], false);
            }
        }
    }
}
