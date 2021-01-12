<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class FutureOrNoEndDate implements SectionValidator
{
    public function isValid($item): bool
    {
        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        $endDate = Carbon::createFromTimestamp(strtotime($item->end));

        return ! $endDate->isPast();
    }

    public function shouldValidate($item): bool
    {
        return ! empty($item->end);
    }
}
