<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Traits\CallbackEligibilityMeter;
use App\ValueObjects\PostmarkCallback\MatchedDataPostmark;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InboundCallbackSingleMatchService
{
    use CallbackEligibilityMeter;

    public function careAmbassadorExistsForPatient( ?int $careAmbassadorId)
    {
        return isset($careAmbassadorId);
    }

    /**
     * @return bool
     */
    public function isCallbackEligible(array $inboundPostmarkData, User $patientUser)
    {
        if ( ! $this->isPatientEnrolled($patientUser)
            || $this->isQueuedForEnrollmentAndUnassigned($patientUser)
            || $this->requestsCancellation($inboundPostmarkData)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isPatientEnrolled(User $patientUser)
    {
        $enrolleeStatus = $patientUser->enrollee->status;

        return Enrollee::ENROLLED === $enrolleeStatus
            && Enrollee::CONSENTED !== $enrolleeStatus
            && Patient::ENROLLED === $patientUser->patientInfo->ccm_status;
    }

    /**
     * @return bool
     */
    public function isQueuedForEnrollmentAndUnassigned(Model $patientUser)
    {
        /** @var Enrollee $enrollee */
        /** @var User $patientUser */
        $enrollee = $patientUser->enrollee;
        if ( ! $enrollee->exists()) {
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
     * @return bool
     */
    public function shouldAssignToCareAmbassador(array $matchedResults)
    {
        return $this->careAmbassadorExistsForPatient($matchedResults['careAmbassadorId'])
            && $this->patientIsNotConsented($matchedResults['enrolleeStatus']);
    }

    /**
     * @param $patientUser
     * @return array
     */
    public function singleMatchCallbackResult($patientUser, array $inboundPostmarkData)
    {
        return $this->singleMatchResult($patientUser, $this->isCallbackEligible($inboundPostmarkData, $patientUser), $inboundPostmarkData);
    }

    /**
     *  Returns the reason why a callback can't be assigned to a $patientUser.
     *
     * @return string
     */
    private function noCallbackReasoning(Model $patientUser, bool $isCallbackEligible, array $inboundPostmarkData)
    {
        if ($isCallbackEligible) {
            return '';
        }

        /** @var User $patientUser */
        if ($this->isQueuedForEnrollmentAndUnassigned($patientUser)) {
            return PostmarkInboundCallbackMatchResults::QUEUED_AND_UNASSIGNED;
        }

        if ($this->requestsCancellation($inboundPostmarkData)) {
            return PostmarkInboundCallbackMatchResults::WITHDRAW_REQUEST;
        }

        if ( ! $this->isPatientEnrolled($patientUser)) {
            return PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
        }

//        If non consented and care_abassador DOES NOT  exists UNTESTED
        if ($this->shouldAssignToCareAmbassador($inboundPostmarkData)){
            return PostmarkInboundCallbackMatchResults::NOT_ENROLLED;
        }
        return 'unmatched';
    }

    private function patientIsNotConsented(string $enrolleeStatus)
    {
        return Enrollee::ENROLLED !== $enrolleeStatus
            && Enrollee::CONSENTED !== $enrolleeStatus
            && Enrollee::QUEUE_AUTO_ENROLLMENT !== $enrolleeStatus;
    }

    /**
     * @return array
     */
    private function singleMatchResult(?Model $matchedPatient, bool $isCallbackEligible, array $inboundPostmarkData)
    {
        return (new MatchedDataPostmark(
            $matchedPatient,
            $isCallbackEligible,
            $this->noCallbackReasoning($matchedPatient, $isCallbackEligible, $inboundPostmarkData)
        ))
            ->getArraySingleMatch();
    }
}
