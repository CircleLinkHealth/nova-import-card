<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Nova\Filters\TwilioCallSourceFilter;
use App\Nova\Metrics\TwilioCallCosts;
use App\Nova\Metrics\TwilioCallDuration;
use CircleLinkHealth\SharedModels\Entities\TwilioCall;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\ValueResult;
use Tests\CustomerTestCase;

class TwilioCallsNovaPageTest extends CustomerTestCase
{
    public function test_cost_metrics_are_correct()
    {
        // Cost: 1 * $0.004 = $0.004
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 30, 0);

        // Cost: (1 * $0.004) + (1 * 0.013) = $0.017
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 50, 45);

        // Cost: (3 * $0.004) + (2 * 0.013) = $0.038
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 165, 118);

        $metric = new TwilioCallCosts('test', TwilioCallSourceFilter::PATIENT_CALL, 4);
        /** @var ValueResult $result */
        $result = $metric->calculate($this->getRequest());
        self::assertEquals(round(0.004 + 0.017 + 0.038, 4), $result->value);
    }

    public function test_duration_metrics_are_correct()
    {
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 30, 0);
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 50, 45);
        $this->addCall(TwilioCallSourceFilter::PATIENT_CALL, 165, 118);

        $metric = new TwilioCallDuration('test', TwilioCallSourceFilter::PATIENT_CALL);
        /** @var ValueResult $result */
        $result = $metric->calculate($this->getRequest());
        self::assertEquals(5, $result->value);
    }

    private function addCall($source, $callDurationSecs, $dialDurationSecs)
    {
        $userId    = TwilioCallSourceFilter::PATIENT_CALL === $source ? $this->careCoach()->id : $this->careAmbassador()->id;
        $patientId = $this->patient()->id;
        TwilioCall::create([
            'call_sid'                 => 'test',
            'source'                   => $source,
            'inbound_user_id'          => $patientId,
            'outbound_user_id'         => $userId,
            'call_duration'            => $callDurationSecs,
            'dial_conference_duration' => $dialDurationSecs,
        ]);
    }

    private function getRequest(): NovaRequest
    {
        return new NovaRequest();
    }
}
