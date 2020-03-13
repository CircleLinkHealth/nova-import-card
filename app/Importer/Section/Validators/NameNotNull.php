<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Validators;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator as SectionValidator;

class NameNotNull implements SectionValidator
{
    public function isValid($item): bool
    {
        return ! (empty($item->translation_name) && empty($item->reference_title) && empty($item->text) && empty($item->product_name) && empty($item->name));
    }

    public function shouldValidate($item): bool
    {
        $keys = collect($item->toArray())->keys();

        return $keys->contains('translation_name') || $keys->contains('reference_title') || $keys->contains('text') || $keys->contains('product_name') || $keys->contains('name');
    }
}
