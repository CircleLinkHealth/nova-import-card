<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Services\Postmark\PostmarkInboundCallbackMatchResults;
use Illuminate\Contracts\Support\Arrayable;

class PostmarkMatchedData implements Arrayable
{
    /**
     * @var array|User[]
     */
    public array $matched;
    public ?string $reasoning;

    public function __construct(array $matched, ?string $reasoning)
    {
        $this->matched   = $matched;
        $this->reasoning = $reasoning;
    }

    public function isMultiMatch(): bool
    {
        return sizeof($this->matched) > 1;
    }

    public function toArray(): array
    {
        return [
            PostmarkInboundCallbackMatchResults::MATCHED_DATA => $this->matched,
            PostmarkInboundCallbackMatchResults::REASONING    => $this->reasoning,
        ];
    }
}
