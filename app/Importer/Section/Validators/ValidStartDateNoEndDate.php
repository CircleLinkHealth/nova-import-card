<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class ValidStartDateNoEndDate implements SectionValidator
{
    public function isValid($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        return true;
    }

    public function shouldValidate($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        return ! empty($item->start) && empty($item->end);
    }
}
