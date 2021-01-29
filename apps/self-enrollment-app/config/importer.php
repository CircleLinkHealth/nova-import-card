<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Allergy\CommaDelimitedListAllergyLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Allergy\JsonListAllergyLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Medication\JsonListMedicationLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Medication\NewLineDelimitedListMedicationLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayCodeAndNameProblemLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayOfProblemForEligibilityCheck;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\ArrayProblemLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\CommaDelimitedListProblemLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Loggers\Problem\JsonListProblemLogger;
use CircleLinkHealth\Eligibility\CcdaImporter\Validators\ImportAllItems;
use CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidEndDate;
use CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidStartDateNoEndDate;
use CircleLinkHealth\Eligibility\CcdaImporter\Validators\ValidStatus;
use CircleLinkHealth\SamlSp\Tests\CircleLinkHealth\Eligibility\CcdaImporter\Validators\NameNotNull;

return [
    'validators' => [
        NameNotNull::class,
        ValidStatus::class,
        ValidEndDate::class,
        ValidStartDateNoEndDate::class,
        ImportAllItems::class,
    ],

    'allergy_loggers' => [
        JsonListAllergyLogger::class,
        CommaDelimitedListAllergyLogger::class,
    ],

    'medication_loggers' => [
        JsonListMedicationLogger::class,
        NewLineDelimitedListMedicationLogger::class,
    ],

    'problem_loggers' => [
        ArrayOfProblemForEligibilityCheck::class,
        JsonListProblemLogger::class,
        CommaDelimitedListProblemLogger::class,
        ArrayProblemLogger::class,
        ArrayCodeAndNameProblemLogger::class,
    ],
];
