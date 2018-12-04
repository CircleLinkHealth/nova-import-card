<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/30/18
 * Time: 2:46 PM
 */

namespace App\Services\Eligibility\Csv\Validators\PatientList;

abstract class BaseValidator implements PatientListValidator
{
    /**
     * @var array
     */
    protected $columnNames = [];

    protected $validator;

    public function isValid()
    {
        return $this->validate() === true;
    }

    /**
     * @return array
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * @param array $columnNames
     *
     * @return SingleFieldsValidator
     */
    public function setColumnNames(array $columnNames)
    {
        $this->columnNames = $columnNames;
    }

    public function errors()
    {
        return $this->validate() === true
            ? null
            : $this->validate();
    }
}
