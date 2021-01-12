<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Eligibility\Lists\Csv\NumberedFieldsValidator;
use CircleLinkHealth\Eligibility\Lists\Csv\PatientListValidator;
use CircleLinkHealth\Eligibility\Lists\Csv\SingleFieldsValidator;
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
     * @var \CircleLinkHealth\Eligibility\Lists\Csv\PatientListValidator
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
