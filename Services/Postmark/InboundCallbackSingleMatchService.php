<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkMatchedData;
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

        if (0 === $patientUser->id || Patient::ENROLLED !== $patientUser->patientInfo->ccm_status) {
            if (Enrollee::CONSENTED === $enrollee->status) {
                return PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
            }

            return isset($enrollee->care_ambassador_user_id)
                ? PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED
                : PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_UNASSIGNED;
        }

        return PostmarkInboundCallbackMatchResults::CREATE_CALLBACK;
    }

    public function getSingleMatchCallbackResult(User $matchedPatient, PostmarkCallbackInboundData $inboundPostmarkData): PostmarkMatchedData
    {
        $callBackEligibleReason = $this->callbackEligibilityReasoning($inboundPostmarkData, $matchedPatient);

        return new PostmarkMatchedData(
            [$matchedPatient],
            $callBackEligibleReason
        );
    }

    public function isQueuedForEnrollmentAndCAUnassigned(User $patientUser): bool
    {
        if (0 === $patientUser->id || ! $patientUser->enrollee) {
            return false;
        }

        return $patientUser->isSurveyOnly()
            && Enrollee::QUEUE_AUTO_ENROLLMENT === $patientUser->enrollee->status
            && Patient::ENROLLED !== $patientUser->patientInfo->ccm_status
            && ! isset($patientUser->enrollee->care_ambassador_user_id);
    }

    public function requestsCancellation(PostmarkCallbackInboundData $postmarkCallbackInboundData): bool
    {
        $cancelReason = $postmarkCallbackInboundData->callbackCancellationMessage();

        return isset($cancelReason)
            || Str::contains(Str::of($postmarkCallbackInboundData->get('message'))->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }
}
