<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Lists\Csv;

interface PatientListValidator
{
    public function errors();

    public function getColumnNames(): array;

    public function isValid();

    public function required();

    public function setColumnNames(array $columnNames);

    /**
     * Validates an array of column names from a CSV that is uploaded to be processed for eligibility.
     * Returns false if there's no errors, and an array of errors if errors are found.
     *
     * @param array $columnNames
     *
     * @return array|bool
     */
    public function validate();
}
