<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Eligibility\Csv;

use App\Services\Eligibility\Csv\Validators\PatientList\NumberedFieldsValidator;
use App\Services\Eligibility\Csv\Validators\PatientList\PatientListValidator;
use App\Services\Eligibility\Csv\Validators\PatientList\SingleFieldsValidator;
use Illuminate\Support\Collection;

class CsvPatientList
{
    /**
     * @var array
     */
    private $columnNames = [];
    /**
     * @var Collection
     */
    private $patientList;

    /**
     * @var PatientListValidator
     */
    private $validator;

    public function __construct(Collection $patientList)
    {
        $this->patientList = $patientList;
    }

    public function getColumnNames()
    {
        if ($this->columnNames) {
            return $this->columnNames;
        }

        $this->columnNames = $this->patientList->isNotEmpty()
            ? array_keys($this->patientList->first())
            : [];

        return $this->columnNames;
    }

    public function guessValidatorAndValidate()
    {
        $validators = [
            new SingleFieldsValidator(),
            new NumberedFieldsValidator(),
        ];

        foreach ($validators as $v) {
            $result = $this->setValidator($v)
                ->validate();

            if (true === $result) {
                return true;
            }

            $this->validator = null;
        }

        return null;
    }

    public function isValid()
    {
        return true === $this->validate();
    }

    /**
     * @param PatientListValidator $validator
     *
     * @return CsvPatientList
     */
    public function setValidator(PatientListValidator $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function validationErrors()
    {
        return $this->validator->isValid()
            ? null
            : $this->validator->errors();
    }

    private function validate()
    {
        if ( ! $this->validator) {
            return null;
        }

        $this->validator->setColumnNames($this->getColumnNames());

        if ($this->validator->isValid()) {
            return true;
        }

        return false;
    }
}
