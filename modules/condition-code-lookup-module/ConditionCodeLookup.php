<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ConditionCodeLookup;

interface ConditionCodeLookup
{
    public function any($value);

    public function icd10($value);

    public function icd9($value);

    public function snomed($value);
}
