<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Contracts\Importer\MedicalRecord\Section\Validator as SectionValidator;
use Carbon\Carbon;

class ValidEndDate implements SectionValidator
{
    public function isValid(ItemLog $item): bool
    {
        if (!$this->shouldValidate($item)) {
            return false;
        }

        $endDate = Carbon::createFromTimestamp(strtotime($item->end));

        return !empty($endDate) && !$endDate->isPast();
    }

    public function shouldValidate(ItemLog $item): bool
    {
        return !empty($item->end);
    }
}
