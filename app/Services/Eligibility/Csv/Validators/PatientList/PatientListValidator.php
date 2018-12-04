<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/30/18
 * Time: 2:09 PM
 */

namespace App\Services\Eligibility\Csv\Validators\PatientList;

interface PatientListValidator
{
    public function isValid();

    public function errors();

    /**
     * Validates an array of column names from a CSV that is uploaded to be processed for eligibility.
     * Returns false if there's no errors, and an array of errors if errors are found.
     *
     * @param array $columnNames
     *
     * @return array|bool
     */
    public function validate();

    public function required();

    public function getColumnNames(): array;

    public function setColumnNames(array $columnNames);
}
