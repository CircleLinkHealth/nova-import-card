<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Services\Postmark;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Services\Postmark\InboundCallbackMultimatchService;
use CircleLinkHealth\SharedModels\Services\Postmark\InboundCallbackSingleMatchService;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * PostmarkInboundCallbackMatchResults constructor.
     */
    public function __construct(PostmarkCallbackInboundData $postmarkCallbackData, int $recordId)
    {
        $this->postmarkCallbackData = $postmarkCallbackData;
        $this->recordId             = $recordId;
    }

    /**
     * Doing the checks separately.
     *
     * @return \CircleLinkHealth\SharedModels\Entities\PostmarkSingleMatchData
     */
    public function matchedPatientsData()
    {
        /** @var Builder $inboundDataMatchedWithPhone */
        $inboundDataMatchedWithPhone = $this->matchByPhone($this->postmarkCallbackData->get('phone'), $this->postmarkCallbackData->get('callerId'));

        if (1 === $inboundDataMatchedWithPhone->count()) {
            /** @var User $matchedPatient */
            $matchedPatient = $inboundDataMatchedWithPhone->first();

            return app(InboundCallbackSingleMatchService::class)
                ->singleMatchCallbackResult($matchedPatient, $this->postmarkCallbackData);
        }

        if ($inboundDataMatchedWithPhone->count() > 1) {
            return app(InboundCallbackMultimatchService::class)
                ->tryToMatchByName($inboundDataMatchedWithPhone->get(), $this->postmarkCallbackData, $this->recordId);
        }
    }

    /**
     * @param string $phone
     * @param string $callerIdField
     *
     * @return Builder|User
     */
    private function matchByPhone(string $phoneNumber, string $callerIdFieldPhone)
    {
        return User::withTrashed()->ofTypePatients()->with([
            'patientInfo' => function ($q) {
                return $q->select(['id', 'ccm_status', 'user_id']);
            },

            'enrollee',

            'phoneNumbers' => function ($q) {
                return $q->select(['id', 'user_id', 'number']);
            },
        ])->searchPhoneNumber([$phoneNumber, $callerIdFieldPhone]);
    }
}
