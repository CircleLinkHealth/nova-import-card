<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects\PostmarkCallback;

use CircleLinkHealth\Eligibility\Entities\Enrollee;

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

    public function getArrayMultimatch()
    {
        return [
            'matchUsersResult' => $this->matchedData,
            'reasoning'        => $this->reasoning,
        ];
    }

    public function getArraySingleMatch()
    {
        return $this->data($this->matchedData->enrollee);
    }

    /**
     * @return array
     */
    private function data(Enrollee $enrollee)
    {
        return [
            'matchUsersResult' => $this->matchedData,
            'reasoning'        => $this->reasoning,
            'enrolleeStatus'   => $enrollee->status,
            'careAmbassadorId' => $enrollee->care_ambassador_user_id,
        ];
    }
}
