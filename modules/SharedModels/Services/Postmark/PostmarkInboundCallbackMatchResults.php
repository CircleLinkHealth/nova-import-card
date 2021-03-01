<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\SharedModels\Entities\PostmarkMatchedData;

class PostmarkInboundCallbackMatchResults
{
    const CREATE_CALLBACK             = 'create_callback';
    const MATCHED_DATA                = 'matchedData';
    const MULTIPLE_PATIENT_MATCHES    = 'multiple_patient_matches';
    const NO_NAME_MATCH_SELF          = 'no_name_match_self';
    const NOT_CONSENTED_CA_ASSIGNED   = 'non_consented_ca_assigned';
    const NOT_CONSENTED_CA_UNASSIGNED = 'non_consented_ca_unassigned';
    const NOT_ENROLLED                = 'not_enrolled';
    const QUEUED_AND_UNASSIGNED       = 'queue_auto_enrollment_and_unassigned';
    const REASONING                   = 'reasoning';
    const SELF                        = 'SELF';
    const WITHDRAW_REQUEST            = 'withdraw_request';

    private PostmarkCallbackInboundData $postmarkCallbackData;
    private int $recordId;

    public function __construct(PostmarkCallbackInboundData $postmarkCallbackData, int $recordId)
    {
        $this->postmarkCallbackData = $postmarkCallbackData;
        $this->recordId             = $recordId;
    }

    public function getMatchResults(): ?PostmarkMatchedData
    {
        $phone                       = $this->postmarkCallbackData->get('phone');
        $callerId                    = $this->postmarkCallbackData->get('callerId');
        $phoneMatchService           = app(InboundCallbackPhoneMatchService::class);
        $inboundDataMatchedWithPhone = $phoneMatchService->getResults($phone, $callerId);
        $count                       = $inboundDataMatchedWithPhone->count();

        if (1 === $count) {
            /** @var User $matchedPatient */
            $matchedPatient     = $inboundDataMatchedWithPhone->first();
            $singleMatchService = app(InboundCallbackSingleMatchService::class);

            return $singleMatchService->getSingleMatchCallbackResult($matchedPatient, $this->postmarkCallbackData);
        }

        if ($count > 1) {
            $multiMatchService = app(InboundCallbackMultiMatchService::class);

            return $multiMatchService->matchByName($inboundDataMatchedWithPhone, $this->postmarkCallbackData, $this->recordId);
        }

        return null;
    }
}
