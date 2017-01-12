<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/01/2017
 * Time: 11:31 PM
 */

namespace App\Importer\Section\Importers;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;
use App\Contracts\Importer\MedicalRecord\Section\Importer as SectionImporter;
use App\Contracts\Importer\MedicalRecord\Section\ItemLog;
use App\Contracts\Importer\MedicalRecord\Section\Validator;

abstract class BaseImporter implements SectionImporter
{
    public function validate(ItemLog $item)
    {
        $validator = $this->chooseValidator($item);

        if (!$validator) {
            return false;
        }

        return $validator->isValid($item);
    }

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

    /**
     * @return Validator[]
     */
    public function validators() : array
    {
        return \Config::get('importer')['validators'];
    }

    abstract public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    );
}