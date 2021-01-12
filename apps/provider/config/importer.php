<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'validators' => [
        \CircleLinkHealth\Eligibility\CcdaImporter\Validators\NameNotNull::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidStatus::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidEndDate::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidStartDateNoEndDate::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Validators\ImportAllItems::class,
    ],

    'allergy_loggers' => [
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Allergy\JsonListAllergyLogger::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Allergy\CommaDelimitedListAllergyLogger::class,
    ],

    'medication_loggers' => [
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Medication\JsonListMedicationLogger::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Medication\NewLineDelimitedListMedicationLogger::class,
    ],

    'problem_loggers' => [
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayOfProblemForEligibilityCheck::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\JsonListProblemLogger::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\CommaDelimitedListProblemLogger::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayProblemLogger::class,
        \CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayCodeAndNameProblemLogger::class,
    ],
];
