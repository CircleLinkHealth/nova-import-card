<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkSingleMatchData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InboundCallbackSingleMatchService
{
    /**
     * @return string
     */
    public function callbackEligibilityReasoning(PostmarkCallbackInboundData $inboundPostmarkData, User $patientUser)
    {
        /** @var Enrollee $enrollee */
        $enrollee = $patientUser->enrollee;

        if ($this->isQueuedForEnrollmentAndCAUnassigned($patientUser)) {
            return PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED;
        }

        if ($this->requestsCancellation($inboundPostmarkData)) {
            return PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST;
        }

        if (Patient::ENROLLED !== $patientUser->patientInfo->ccm_status) {
            if (Enrollee::CONSENTED === $enrollee->status) {
                return PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
            }

            if (isset($enrollee->care_ambassador_user_id)) {
                return PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED;
            }

            return PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_UNASSIGNED;
        }

        return PostmarkInboundCallbackMatchResults::CREATE_CALLBACK;
    }

    /**
     * @return bool
     */
    public function isQueuedForEnrollmentAndCAUnassigned(Model $patientUser)
    {
        /** @var Enrollee $enrollee */
        /** @var User $patientUser */
        $enrollee = $patientUser->enrollee;
        if ( ! $enrollee) {
            return false;
        }

        return $patientUser->isSurveyOnly()
            && Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status
            && Patient::ENROLLED !== $patientUser->patientInfo->ccm_status
            && ! isset($enrollee->care_ambassador_user_id);
    }

    /**
     * @param $postmarkCallbackInboundData
     * @return bool
     */
    public function requestsCancellation(PostmarkCallbackInboundData $postmarkCallbackInboundData)
    {
        $cancelReason = $postmarkCallbackInboundData->callbackCancellationMessage();

        return isset($cancelReason)
            || Str::contains(Str::of($postmarkCallbackInboundData->get('message'))->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }

    /**
     * @return PostmarkSingleMatchData
     */
    public function singleMatchCallbackResult(User $matchedPatient, PostmarkCallbackInboundData $inboundPostmarkData)
    {
        $callBackEligibleReason = $this->callbackEligibilityReasoning($inboundPostmarkData, $matchedPatient);

        return new PostmarkSingleMatchData(
            $matchedPatient,
            $callBackEligibleReason
        );
    }
}
