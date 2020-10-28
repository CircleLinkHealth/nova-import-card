<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

use CircleLinkHealth\Customer\Entities\User;

class PostmarkSingleMatchData
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
     * @return array
     */
    public function getMatchedData()
    {
        return [
            'matchUsersResult' => $this->matchedUser,
            'reasoning'        => $this->reasoning,
        ];
    }
}
