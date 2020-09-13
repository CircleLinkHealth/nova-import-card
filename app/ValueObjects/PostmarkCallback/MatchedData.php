<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

class MatchedData
{
    private $matchedData;
    private array $reasoning;
    private bool $shouldCreateCallback;

    /**
     * MatchedDataResult constructor.
     * @param $matchedData
     */
    public function __construct($matchedData, bool $shouldCreateCallback, array $reasoning = [])
    {
        $this->matchedData          = $matchedData;
        $this->shouldCreateCallback = $shouldCreateCallback;
        $this->reasoning            = $reasoning;
    }

    public function getArray()
    {
        return [
            'matchResult'    => $this->matchedData,
            'createCallback' => $this->shouldCreateCallback,
            'reasoning'      => json_encode($this->reasoning),
        ];
    }
}
