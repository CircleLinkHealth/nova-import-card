<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class PostmarkInboundCallbackMatchResults
{
    const CREATE_CALLBACK             = 'create_callback';
    const MULTIPLE_PATIENT_MATCHES    = 'multiple_patient_matches';
    const NO_NAME_MATCH_SELF          = 'no_name_match_self';
    const NOT_CONSENTED_CA_ASSIGNED   = 'non_consented_ca_assigned';
    const NOT_CONSENTED_CA_UNASSIGNED = 'non_consented_ca_unassigned';
    const NOT_ENROLLED                = 'not_enrolled';
    const QUEUED_AND_UNASSIGNED       = 'queue_auto_enrollment_and_unassigned';
    const SELF                        = 'SELF';
    const WITHDRAW_REQUEST            = 'withdraw_request';

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
     * Doing the checks separately.
     *
     * @return array|Builder|void
     */
    public function matchedPatientsData()
    {
        /** @var Builder $inboundDataMatchedWithPhone */
        $inboundDataMatchedWithPhone = $this->matchByPhone($this->postmarkCallbackData['phone'], $this->postmarkCallbackData['callerId']);

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
        return User::withTrashed()->ofType('participant')->with([
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
