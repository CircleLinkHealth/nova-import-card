<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Settings;

use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\GetProblemInstruction;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\GetUPG0506ProblemInstruction;
use CircleLinkHealth\Eligibility\CcdaImporter\Hooks\ReplaceFieldsFromSupplementaryData;

class Hooks
{
    const LISTENERS = [
        ReplaceFieldsFromSupplementaryData::IMPORTING_LISTENER_NAME => ReplaceFieldsFromSupplementaryData::class,
        GetProblemInstruction::IMPORTING_LISTENER_NAME              => GetProblemInstruction::class,
        GetUPG0506ProblemInstruction::IMPORTING_LISTENER_NAME       => GetUPG0506ProblemInstruction::class,
    ];
}
