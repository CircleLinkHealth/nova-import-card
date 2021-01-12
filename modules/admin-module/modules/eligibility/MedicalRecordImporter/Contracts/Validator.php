<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts;

/**
 * This is a Section Validator. We use it to decide whether the data is Valid and should be imported.
 *
 * Interface Validator
 */
interface Validator
{
    public function isValid($item): bool;

    public function shouldValidate($item): bool;
}
