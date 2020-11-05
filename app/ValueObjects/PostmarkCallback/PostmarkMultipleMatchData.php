<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

use Illuminate\Support\Collection;

class PostmarkMultipleMatchData
{
    private $matchedData;
    /**
     * @var null
     */
    private $reasoning;

    /**
     * MatchedData constructor.
     */
    public function __construct(Collection $matchedData, ?string $reasoning)
    {
        $this->matchedData = $matchedData;
        $this->reasoning   = $reasoning;
    }

    /**
     * @return array
     */
    public function getMatchedData()
    {
        return [
            'matchUsersResult' => $this->matchedData,
            'reasoning'        => $this->reasoning,
        ];
    }
}
