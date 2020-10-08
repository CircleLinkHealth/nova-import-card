<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait CallbackEligibilityMeter
// Help naming this trait?
{
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
        return Enrollee::ENROLLED === $patientUser->enrollee->status
            && Patient::ENROLLED  === $patientUser->patientInfo->ccm_status;
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
            && is_null($enrollee->toArray()['care_ambassador_user_id']);
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

        return 'unmatched';
    }

    private function singleMatch(Collection $postmarkInboundPatientsMatched)
    {
        return 1 === $postmarkInboundPatientsMatched->count();
    }
}
