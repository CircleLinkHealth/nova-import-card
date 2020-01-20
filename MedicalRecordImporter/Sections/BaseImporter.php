<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Sections;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Importer as SectionImporter;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator;
use CircleLinkHealth\Eligibility\Contracts\ImportedMedicalRecord;

abstract class BaseImporter implements SectionImporter
{
    public function chooseValidator($item)
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

    public function validate($item)
    {
        $validator = $this->chooseValidator($item);

        if ( ! $validator) {
            return false;
        }

        return $validator->isValid($item);
    }

    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\Validator[]
     */
    public function validators(): array
    {
        return \config('importer')['validators'];
    }
}
