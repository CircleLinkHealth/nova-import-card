<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 07/01/2017
 * Time: 1:31 AM
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

/**
 * This is a Section Importer. It allows for each Health Section to be able to be imported for QA.
 *
 * Interface Importer
 * @package App\Contracts\Importer\MedicalRecord\Section
 */
interface Importer
{
    public function import(
        $healthRecordId,
        $healthRecordType
    );

    public function chooseValidator(ItemLog $item);

    public function validate(ItemLog $item);

    /**
     * @return Validator[]
     */
    public function validators() : array;
}