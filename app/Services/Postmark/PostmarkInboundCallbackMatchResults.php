<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Traits\CallbackEligibilityMeter;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PostmarkInboundCallbackMatchResults
{
    use CallbackEligibilityMeter;
    const NO_NAME_MATCH         = 'no_name_match';
    const NO_NAME_MATCH_SELF    = 'no_name_match_self';
    const NOT_ENROLLED          = 'not_enrolled';
    const QUEUED_AND_UNASSIGNED = 'queue_auto_enrollment_and_unassigned';
    const SELF                  = 'SELF';
    const WITHDRAW_REQUEST      = 'withdraw_request';

    private array $postmarkCallbackData;
    private int $recordId;

    /**
     * PostmarkInboundCallbackMatchResults constructor.
     */
    public function __construct(array $postmarkCallbackData, int $recordId)
    {
        $this->postmarkCallbackData = $postmarkCallbackData;
        $this->recordId             = $recordId;
    }

    /**
     * @return array|Builder|void
     */
    public function matchedPatientsData()
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($this->postmarkCallbackData);
        if ($this->singleMatch($postmarkInboundPatientsMatched->get())) {
            $matchedPatient = $postmarkInboundPatientsMatched->first();
            return app(InboundCallbackSingleMatchService::class)->singleMatchCallbackResult($matchedPatient, $this->postmarkCallbackData);
        }

        if ($this->multiMatch($postmarkInboundPatientsMatched)) {
            return app(InboundCallbackMultimatchService::class)
                ->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched->get(), $this->postmarkCallbackData, $this->recordId);
        }

        Log::warning("Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
        sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
    }

    /**
     * @return Builder|User
     */
    private function getPostmarkInboundPatientsByPhone(array $inboundPostmarkData)
    {
        return User::ofType('participant')
            ->with('patientInfo', 'enrollee', 'phoneNumbers') //Get only what you need from each relationship mate.
            ->whereHas('phoneNumbers', function ($phoneNumber) use ($inboundPostmarkData) {
                $phoneNumber->where('number', $inboundPostmarkData['Phone']);
            });
    }

    /**
     * @return bool
     */
    private function multiMatch(Builder $postmarkInboundPatientsMatched)
    {
        return $postmarkInboundPatientsMatched->count() > 1;
    }
}
