<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

class FirstNameLastNameValueObject
{
    public function firstsLastNameArray(array $patientNameArray)
    {
        return [
            'firstName' => isset($patientNameArray[1]) ? $patientNameArray[1] : '',
            'lastName'  => isset($patientNameArray[2]) ? $patientNameArray[2] : '',
        ];
    }
}
