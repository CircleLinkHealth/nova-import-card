<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use Illuminate\Contracts\Support\Arrayable;

class PostmarkSingleMatchData implements Arrayable
{
    private User $matchedPatient;
    private ?string $reasoning;

    /**
     * PostmarkSingleMatchData constructor.
     */
    public function __construct(User $matchedPatient, ?string $reasoning)
    {
        $this->matchedPatient = $matchedPatient;
        $this->reasoning      = $reasoning;
    }

    /**
     * @return User
     */
    public function matchedData()
    {
        return $this->matchedPatient;
    }

    /**
     * @return string|null
     */
    public function reasoning()
    {
        return $this->reasoning;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            PostmarkInboundCallbackMatchResults::MATCHED_DATA => $this->matchedPatient,
            PostmarkInboundCallbackMatchResults::REASONING    => $this->reasoning,
        ];
    }
}
