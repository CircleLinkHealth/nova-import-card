<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

class MatchedData
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
    public function __construct($matchedData, bool $shouldCreateCallback, $reasoning = '')
    {
        $this->matchedData          = $matchedData;
        $this->shouldCreateCallback = $shouldCreateCallback;
        $this->reasoning            = $reasoning;
    }

    public function getArray()
    {
        return [
            'matchUsersResult' => $this->matchedData,
            'createCallback'   => $this->shouldCreateCallback,
            'reasoning'        => $this->reasoning,
        ];
    }
}
