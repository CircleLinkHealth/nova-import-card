<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\ValueObjects\PostmarkCallback\MatchedDataPostmark;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InboundCallbackSingleMatchService
{
    /**
     * @return string
     */
    public function callbackEligibilityReasoning(array $inboundPostmarkData, User $patientUser)
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

        return Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status
            && ! isset($enrollee->care_ambassador_user_id);
    }

    /**
     * @param $postmarkData
     * @return bool
     */
    public function requestsCancellation(array $postmarkData)
    {
        return isset($postmarkData['Cancel/Withdraw Reason'])
            || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }

    /**
     * @param $patientUser
     * @return array
     */
    public function singleMatchCallbackResult(User $patientUser, array $inboundPostmarkData)
    {
        return $this->singleMatchResult($patientUser, $inboundPostmarkData);
    }

    /**
     * @return array
     */
    private function singleMatchResult(?User $matchedPatient, array $inboundPostmarkData)
    {
        $callBackEligibleReason = $this->callbackEligibilityReasoning($inboundPostmarkData, $matchedPatient);

        return (new MatchedDataPostmark(
            $matchedPatient,
            $callBackEligibleReason
        ))->getMatchedData();
    }
}
