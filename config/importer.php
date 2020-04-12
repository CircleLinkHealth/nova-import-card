<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'validators' => [
        \App\Importer\Section\Validators\NameNotNull::class,
        \App\Importer\Section\Validators\ValidStatus::class,
        \App\Importer\Section\Validators\ValidEndDate::class,
        \App\Importer\Section\Validators\ValidStartDateNoEndDate::class,
        \App\Importer\Section\Validators\ImportAllItems::class,
    ],

    'allergy_loggers' => [
        App\Importer\Loggers\Allergy\JsonListAllergyLogger::class,
        App\Importer\Loggers\Allergy\CommaDelimitedListAllergyLogger::class,
    ],

    'medication_loggers' => [
        App\Importer\Loggers\Medication\JsonListMedicationLogger::class,
        App\Importer\Loggers\Medication\NewLineDelimitedListMedicationLogger::class,
    ],

    'problem_loggers' => [
        App\Importer\Loggers\Problem\ArrayOfProblemForEligibilityCheck::class,
        App\Importer\Loggers\Problem\JsonListProblemLogger::class,
        App\Importer\Loggers\Problem\CommaDelimitedListProblemLogger::class,
        App\Importer\Loggers\Problem\ArrayProblemLogger::class,
        App\Importer\Loggers\Problem\ArrayCodeAndNameProblemLogger::class,
    ],
];
