<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

class PatientCareplanRelations
{
    public static function get()
    {
        return [
            'appointments' => function ($q) {
                $q->orderBy('id', 'desc')->with('provider')->take(5);
            },
            'carePlan',
            'carePlanAssessment' => function ($q) {
                $q->whereNotNull('key_treatment');
            },
            'ccdInsurancePolicies',
            'ccdAllergies',
            'ccdMedications',
            'ccdProblems.cpmInstruction',
            'ccdProblems.codes',
            'ccdProblems.cpmProblem',
            'cpmMiscUserPivot.cpmInstruction',
            'cpmMiscUserPivot.cpmMisc',
            'cpmSymptoms',
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
