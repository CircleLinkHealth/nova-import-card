<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Traits\CallbackEligibilityMeter;
use App\ValueObjects\PostmarkCallback\MatchedData;
use Illuminate\Database\Eloquent\Model;

class InboundCallbackSingleMatchService
{
    use CallbackEligibilityMeter;

    /**
     * @param $patientUser
     * @return array
     */
    public function singleMatchCallbackCandidate($patientUser, array $inboundPostmarkData)
    {
        return $this->singleMatchResult($patientUser, $this->isCallbackEligible($inboundPostmarkData, $patientUser), $inboundPostmarkData);
    }

    /**
     * @return array
     */
    private function singleMatchResult(?Model $matchedPatient, bool $isCallbackEligible, array $inboundPostmarkData)
    {
        return (new MatchedData(
            $matchedPatient,
            $isCallbackEligible,
            $this->noCallbackReasoning($matchedPatient, $isCallbackEligible, $inboundPostmarkData)
        ))
            ->getArray();
    }
}
