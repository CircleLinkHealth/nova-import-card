<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Importer\MedicalRecord\Section;

/**
 * This is a Section Validator. We use it to decide whether the data is Valid and should be imported.
 *
 * Interface Validator
 */
interface Validator
{
    public function isValid(ItemLog $item): bool;

    public function shouldValidate(ItemLog $item): bool;
}
