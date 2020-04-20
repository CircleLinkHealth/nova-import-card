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
        if (is_array($item)) {
            $item = (object) $item;
        }

        return ! (empty($item->translation_name) && empty($item->reference_title) && empty($item->text) && empty($item->product_name) && empty($item->name));
    }

    public function shouldValidate($item): bool
    {
        if (is_array($item)) {
            $item = (object) $item;
        }

        if (method_exists($item, 'toArray')) {
            $keys = collect($item->toArray())->keys();
        } elseif (is_array($item)) {
            $keys = collect(array_keys($item));
        } else {
            $keys = collect(array_keys((array) $item));
        }

        return $keys->contains('translation_name') || $keys->contains('reference_title') || $keys->contains('text') || $keys->contains('product_name') || $keys->contains('name');
    }
}
