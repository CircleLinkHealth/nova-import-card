<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Relationships;

class PatientCareplanRelations
{
    public static function get()
    {
        return [
            'appointments' => function ($q) {
                $q->orderBy('id', 'desc')->with('provider')->paginate();
            },
            'carePlan' => function ($q) {
                return $q->withNurseApprovedVia();
            },
            'carePlanAssessment' => function ($q) {
                $q->whereNotNull('key_treatment');
            },
            'ccdInsurancePolicies',
            'ccdAllergies' => function ($q) {
                $q->orderBy('allergen_name');
            },
            'ccdMedications' => function ($q) {
                $q->orderBy('name');
            },
            'ccdProblems.cpmInstruction',
            'ccdProblems.codes',
            'ccdProblems.cpmProblem',
            'cpmMiscUserPivot.cpmInstruction',
            'cpmMiscUserPivot.cpmMisc',
            'cpmSymptoms' => function ($q) {
                $q->orderBy('name');
            },
            'cpmProblems',
            'cpmLifestyles',
            'cpmBiometrics',
            'cpmWeight',
            'cpmBloodSugar',
            'cpmSmoking',
            'cpmSymptoms',
            'cpmMedicationGroups',
        ];
    }
}
