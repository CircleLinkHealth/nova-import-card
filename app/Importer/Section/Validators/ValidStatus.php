<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class ValidStatus implements SectionValidator
{
    public function isValid($item): bool
    {
        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        return true;
    }

    public function shouldValidate($item): bool
    {
        return empty($item->status)
            ? false
            : in_array(strtolower($item->status), [
                'active',
                'chronic',
            ]);
    }
}
