<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkCallbackMailService
{
    /**
     * @param User $patientUser
     * @return bool
     */
    public function isPatientEnrolled(User $patientUser)
    {
        return Patient::ENROLLED === $patientUser->enrollee->status
            && Patient::ENROLLED === $patientUser->patientInfo->ccm_status;
    }

    /**
     * @return bool
     */
    public function isQueuedForEnrollmentAndUnassigned(User $patientUser)
    {
        if ( ! $patientUser->enrollee->exists()) {
            return false;
        }

        return Enrollee::QUEUE_AUTO_ENROLLMENT === $patientUser->enrollee->status
            && is_null($patientUser->enrollee->care_ambassador_user_id);
    }

    /**
     * @return array|void
     */
    public function postmarkInboundData(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        return collect(json_decode($postmarkRecord->data))->toArray();
    }

    /**
     * @param $postmarkData
     * @return bool
     */
    public function requestsCancellation($postmarkData)
    {
        return isset($postmarkData['Cancel/Withdraw Reason'])
            || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }

    /**
     * @return mixed
     */
    public function shouldCreateCallBackFromPostmarkInbound(array $matchedPatients)
    {
        return $matchedPatients['createCallback'];
    }
    
    /**
     * @param array $inboundPostmarkData
     * @param User $patientUser
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
}
