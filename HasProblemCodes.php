<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels;

use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;

trait HasProblemCodes
{
    public function codeMap()
    {
        $map = collect();

        $icd9 = $this->icd9Codes->first();
        if ($icd9) {
            $map[ProblemCodeSystem::ICD9] = $icd9->code;
        }

        $icd10 = $this->icd10Codes->first();
        if ($icd10) {
            $map[ProblemCodeSystem::ICD10] = $icd10->code;
        }

        $snomed = $this->snomedCodes->first();
        if ($snomed) {
            $map[ProblemCodeSystem::SNOMED] = $snomed->code;
        }

        return $map;
    }

    public function icd10Codes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', $this->getProblemCodeTypeId(ProblemCodeSystem::ICD10_NAME));
    }

    public function icd9Codes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', $this->getProblemCodeTypeId(ProblemCodeSystem::ICD9_NAME));
    }

    public function snomedCodes()
    {
        return $this->codes()
            ->where('problem_code_system_id', '=', $this->getProblemCodeTypeId(ProblemCodeSystem::SNOMED_NAME));
    }
    
    public function getProblemCodeTypeId(string $codeType) {
        return \Cache::remember(sha1("HasProblemCodes:$codeType"), 2, function () use ($codeType){
            return optional(ProblemCodeSystem::whereName($codeType)->first())->id;
        });
    }
}
