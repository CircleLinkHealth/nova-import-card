<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 1:31 AM
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

use App\Contracts\Importer\ImportedMedicalRecord\ImportedMedicalRecord;

/**
 * This is a Section Importer. It allows for each Health Section to be able to be imported for QA.
 *
 * Interface Importer
 * @package App\Contracts\Importer\MedicalRecord\Section
 */
interface Importer
{
    /**
     * This will import a Section (eg. Problems, Demographics, Meds), and attach it to an ImportedMedicalRecord for QA.
     *
     * @param $medicalRecordId
     * @param $medicalRecordType
     * @param ImportedMedicalRecord $importedMedicalRecord
     *
     * @return mixed
     */
    public function import(
        $medicalRecordId,
        $medicalRecordType,
        ImportedMedicalRecord $importedMedicalRecord
    );

    public function chooseValidator(ItemLog $item);

    public function validate(ItemLog $item);

    /**
     * @return Validator[]
     */
    public function validators() : array;
}
