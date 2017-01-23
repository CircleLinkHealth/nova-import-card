<?php

namespace App\Importer\Section\Validators;


use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Contracts\Importer\MedicalRecord\Section\Validator as SectionValidator;


class ImportAllItems implements SectionValidator
{
    public function isValid(ItemLog $item) : bool
    {
        if (!$this->shouldValidate($item)) {
            return false;
        }

        return true;
    }

    public function shouldValidate(ItemLog $item) : bool
    {
        return empty($item->status)
        && empty($item->start)
        && empty($item->end);
    }
}