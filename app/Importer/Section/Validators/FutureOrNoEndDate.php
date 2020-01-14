<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;


use App\Contracts\Importer\MedicalRecord\Section\Validator as SectionValidator;
use Carbon\Carbon;

class FutureOrNoEndDate implements SectionValidator
{
    public function isValid(ItemLog $item): bool
    {
        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        $endDate = Carbon::createFromTimestamp(strtotime($item->end));

        return ! $endDate->isPast();
    }

    public function shouldValidate(ItemLog $item): bool
    {
        return ! empty($item->end);
    }
}
