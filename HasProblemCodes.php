<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CarePlanModels;

use App\Constants;

trait HasProblemCodes
{
    public function codeMap()
    {
        $map = collect();

        $icd9 = $this->icd9Codes->first();
        if ($icd9) {
            $map[Constants::ICD9] = $icd9->code;
        }

        $icd10 = $this->icd10Codes->first();
        if ($icd10) {
            $map[Constants::ICD10] = $icd10->code;
        }

        $snomed = $this->snomedCodes->first();
        if ($snomed) {
            $map[Constants::SNOMED] = $snomed->code;
        }

        return $map;
    }

    public function icd10Codes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', '2');
    }

    public function icd9Codes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', '1');
    }

    public function snomedCodes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', '3');
    }
}
