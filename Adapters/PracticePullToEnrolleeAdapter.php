<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;

class PracticePullToEnrolleeAdapter
{
    protected string $mrn;
    protected int $practiceId;
    protected ?Demographics $demographics;

    public function __construct(string $mrn, int $practiceId)
    {
        $this->mrn = $mrn;
        $this->practiceId = $practiceId;
    }

    public function toArray() : array
    {
        if (is_null($this->demographics)){
            return [];
        }

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

    public static function getArray(string $mrn, int $practiceId): array
    {
        return (new static($mrn, $practiceId))
            ->setPracticePullDemographics()
            ->toArray();
    }

    private function setPracticePullDemographics():self
    {
        $this->demographics = Demographics::where('practice_id', $this->practiceId)
            ->where('mrn', $this->mrn)
            ->first();

        return $this;
    }
}
