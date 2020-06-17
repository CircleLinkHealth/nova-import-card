<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class ImportAllItems implements SectionValidator
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

        return
            //do not count status 'completed' as valid input because it's not reliable, and only found in meds.
            ! in_array(strtolower($item->status), ValidStatus::VALID_STATUS_VALUES)
        && empty($item->start)
        && empty($item->end);
    }
}
