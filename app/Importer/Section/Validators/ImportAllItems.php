<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use App\Contracts\Importer\MedicalRecord\Section\Validator as SectionValidator;

class ImportAllItems implements SectionValidator
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
        && empty($item->start)
        && empty($item->end);
    }
}
