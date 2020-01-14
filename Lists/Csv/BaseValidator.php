<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Lists\Csv;

abstract class BaseValidator implements PatientListValidator
{
    /**
     * @var array
     */
    protected $columnNames = [];

    protected $validator;

    public function errors()
    {
        return true === $this->validate()
            ? null
            : $this->validate();
    }

    /**
     * @return array
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    public function isValid()
    {
        return true === $this->validate();
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
}
