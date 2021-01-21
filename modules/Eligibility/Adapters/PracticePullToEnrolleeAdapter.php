<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Adapters;

use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics;

class PracticePullToEnrolleeAdapter
{
    protected ?Demographics $demographics;
    protected string $mrn;
    protected int $practiceId;

    public function __construct(string $mrn, int $practiceId)
    {
        $this->mrn        = $mrn;
        $this->practiceId = $practiceId;
    }

    public static function getArray(string $mrn, int $practiceId): array
    {
        return (new static($mrn, $practiceId))
            ->setPracticePullDemographics()
            ->toArray();
    }

    public function toArray(): array
    {
        if (is_null($this->demographics)) {
            return [];
        }

        return [
            'email'      => $this->demographics->email,
            'first_name' => $this->demographics->first_name,
            'last_name'  => $this->demographics->last_name,
            'dob'        => $this->demographics->dob,
            'gender'     => $this->demographics->gender,
            'lang'       => $this->demographics->lang,

            'location_id' => $this->demographics->location_id,
            'provider_id' => $this->getProviderId(),
            'address'   => $this->demographics->street,
            'address_2' => $this->demographics->street2,

            'city'  => $this->demographics->city,
            'state' => $this->demographics->state,
            'zip'   => $this->demographics->zip,

            'home_phone'  => $this->demographics->home_phone,
            'cell_phone'  => $this->demographics->cell_phone,
            'other_phone' => $this->demographics->other_phone,

            'status'         => Enrollee::TO_CALL,
            'source'         => Enrollee::SOURCE_PRACTICE_PULL,
            'last_encounter' => $this->demographics->last_encounter,
            'facility_name'  => $this->demographics->facility_name,

            'primary_insurance'   => $this->demographics->primary_insurance,
            'secondary_insurance' => $this->demographics->secondary_insurance,
            'tertiary_insurance'  => $this->demographics->tertiary_insurance,

            'referring_provider_name' => $this->demographics->referring_provider_name,
        ];
    }

    private function setPracticePullDemographics(): self
    {
        $this->demographics = Demographics::where('practice_id', $this->practiceId)
            ->where('mrn', $this->mrn)
            ->first();

        return $this;
    }

    private function getProviderId():? int
    {
        return $this->demographics->billing_provider_user_id ?? optional(CcdaImporterWrapper::mysqlMatchProvider($this->demographics->referring_provider_name, $this->practiceId))->id;
    }
}
