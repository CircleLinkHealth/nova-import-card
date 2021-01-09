<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\SharedModels\Entities\Enrollee;

class PracticePullToEnrolleeAdapter
{
    public static function getArray(string $mrn, int $practiceId): array
    {
        return [
            'email',
            'first_name',
            'last_name',
            'dob',
            'gender',
            'lang',
            'location_id',
            'provider_id',
            'address',
            'address_2',

            'city',
            'state',
            'zip',

            'home_phone',
            'cell_phone',
            'other_phone',
            'primary_insurance',
            'secondary_insurance',
            'status' => Enrollee::TO_CALL,
            'source' => Enrollee::SOURCE_PRACTICE_PULL,
            'last_encounter',
            'facility_name',

            'primary_insurance',
            'secondary_insurance',
            'tertiary_insurance',
            'last_encounter',
            'referring_provider_name',
            'problems',
            'cpm_problem_1',
            'cpm_problem_2',
        ];
    }
}
