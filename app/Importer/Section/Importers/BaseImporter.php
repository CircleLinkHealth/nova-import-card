<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Importer\Section\Importers;

use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;
use App\Contracts\Importer\MedicalRecord\Section\Importer as SectionImporter;

use App\Contracts\Importer\MedicalRecord\Section\Validator;

abstract class BaseImporter implements SectionImporter
{
    public function chooseValidator(ItemLog $item)
    {
        foreach ($this->validators() as $className) {
            $validator = app($className);

            if ($validator->shouldValidate($item)) {
                return $validator;
            }
        }

        return false;
    }

    abstract public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    );

    public function validate(ItemLog $item)
    {
        $validator = $this->chooseValidator($item);

        if ( ! $validator) {
            return false;
        }

        return $validator->isValid($item);
    }

    /**
     * @return Validator[]
     */
    public function validators(): array
    {
        return \config('importer')['validators'];
    }
}
