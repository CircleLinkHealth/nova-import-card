<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Entities;

use App\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;

class PostmarkSingleMatchData implements Arrayable
{
    private User $matchedUser;
    private ?string $reasoning;

    /**
     * PostmarkSingleMatchData constructor.
     */
    public function __construct(User $matchedUser, ?string $reasoning)
    {
        $this->matchedUser = $matchedUser;
        $this->reasoning   = $reasoning;
    }

    /**
     * @return User
     */
    public function matchedData()
    {
        return $this->matchedUser;
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
            PostmarkInboundCallbackMatchResults::MATCHED_DATA => $this->matchedUser,
            PostmarkInboundCallbackMatchResults::REASONING    => $this->reasoning,
        ];
    }
}
