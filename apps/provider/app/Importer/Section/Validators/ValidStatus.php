<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class ValidStatus implements SectionValidator
{
    /**
     * Only these values will be considered as "valid candidates" for the validation process.
     */
    const VALID_STATUS_VALUES = [
        'active',
        'inactive',
        'chronic',
        'taking',
        'continue',
        'refill',
        'not taking',
    ];

    public function isValid($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        if ( ! $this->shouldValidate($item)) {
            return false;
        }

        if (in_array(strtolower($item->status), [
            'active',
            'chronic',
            'taking',
            'continue',
            'refill',
        ])) {
            return true;
        }

        return false;
    }

    public function shouldValidate($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        return empty($item->status)
            ? false
            : in_array(strtolower($item->status), self::VALID_STATUS_VALUES);
    }
}
