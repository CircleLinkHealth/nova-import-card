<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Contracts\Importer\MedicalRecord\Section\Validator as SectionValidator;

class ValidStatus implements SectionValidator
{
    public function isValid(ItemLog $item): bool
    {
        if (!$this->shouldValidate($item)) {
            return false;
        }

        return true;
    }

    public function shouldValidate(ItemLog $item): bool
    {
        return empty($item->status)
            ? false
            : in_array(strtolower($item->status), [
                'active',
                'chronic',
            ]);
    }
}
