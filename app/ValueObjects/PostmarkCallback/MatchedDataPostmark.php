<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;


class MatchedDataPostmark
{
    private $matchedData;
    /**
     * @var null
     */
    private $reasoning;
    private bool $shouldCreateCallback;

    /**
     * MatchedData constructor.
     * @param $matchedData
     * @param string $reasoning
     */
    public function __construct($matchedData, ?string $reasoning)
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
