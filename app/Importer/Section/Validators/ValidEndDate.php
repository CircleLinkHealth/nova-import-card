<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class ValidEndDate implements SectionValidator
{
    public function isValid($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        $endDate = Carbon::createFromTimestamp(strtotime($item->end));

        return ! empty($endDate) && ! $endDate->isPast();
    }

    public function shouldValidate($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        return ! empty($item->end);
    }
}
