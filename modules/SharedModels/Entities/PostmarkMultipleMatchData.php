<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PostmarkMultipleMatchData implements Arrayable
{
    private $matchedPatients;
    /**
     * @var null
     */
    private $reasoning;

    /**
     * MatchedData constructor.
     */
    public function __construct(Collection $matchedPatients, ?string $reasoning)
    {
        $this->matchedPatients = $matchedPatients;
        $this->reasoning       = $reasoning;
    }

    /**
     * @return Collection
     */
    public function matchedData()
    {
        return $this->matchedPatients;
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
            PostmarkInboundCallbackMatchResults::MATCHED_DATA => $this->matchedPatients,
            PostmarkInboundCallbackMatchResults::REASONING    => $this->reasoning,
        ];
    }
}
