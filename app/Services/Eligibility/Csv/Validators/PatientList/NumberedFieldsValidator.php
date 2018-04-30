<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/30/18
 * Time: 2:18 PM
 */

namespace App\Services\Eligibility\Csv\Validators\PatientList;


class NumberedFieldsValidator implements PatientListValidator
{
    public function isValid()
    {
        // TODO: Implement isValid() method.
    }

    public function errors()
    {
        // TODO: Implement errors() method.
    }

    /**
     * Validates an array of column names from a CSV that is uploaded to be processed for eligibility.
     * Returns false if there's no errors, and an array of errors if errors are found.
     *
     * @param array $columnNames
     *
     * @return array|bool
     */
    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function required()
    {
        // TODO: Implement required() method.
    }

    public function getColumnNames(): array
    {
        // TODO: Implement getColumnNames() method.
    }

    public function setColumnNames(array $columnNames)
    {
        // TODO: Implement setColumnNames() method.
        return $this;
    }
}