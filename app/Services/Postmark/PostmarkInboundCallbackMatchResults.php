<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\ValueObjects\PostmarkCallback\MatchedData;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkInboundCallbackMatchResults
{
    const NO_NAME_MATCH         = 'no_name_match';
    const NO_NAME_MATCH_SELF    = 'no_name_match_self';
    const NOT_ENROLLED          = 'not_enrolled';
    const QUEUED_AND_UNASSIGNED = 'queue_auto_enrollment_and_unassigned';
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
        return  Patient::ENROLLED === $patientUser->enrollee->status
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
     * @return array|Builder|void
     */
    public function matchedPatientsData()
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($this->postmarkCallbackData);

        if ($this->singleMatch($postmarkInboundPatientsMatched->get())) {
            return (new MatchedData(
                $postmarkInboundPatientsMatched->first(),
                $this->patientIsCallbackEligible(
                    $postmarkInboundPatientsMatched->first(),
                    $this->postmarkCallbackData
                ),
                $this->noCallbackReasoning($postmarkInboundPatientsMatched->first())
            ))
                ->getArray();
        }

        if ($this->multiMatch($postmarkInboundPatientsMatched)) {
            return  $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched->get(), $this->postmarkCallbackData);
        }

        Log::warning("Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
        sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
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
     * @return array|Builder|void
     */
    private function filterPostmarkInboundPatientsByName(Collection $patientsMatchedByPhone, array $inboundPostmarkData)
    {
        if ('SELF' === $inboundPostmarkData['Ptn']) {
            return $this->matchByCallerField($patientsMatchedByPhone, $inboundPostmarkData, $this->recordId);
        }

        $patientsMatchWithInboundName = $patientsMatchedByPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);

        if ($patientsMatchWithInboundName->isEmpty() ||  1 !== $patientsMatchWithInboundName->count()) {
            sendSlackMessage('#carecoach_ops_alerts', "Inbound callback with record id:$this->recordId was matched with phone but failed to match with user name.");

            return (new MatchedData(
                $patientsMatchedByPhone,
                false,
                self::NO_NAME_MATCH
            ))
                ->getArray();
        }

        return (new MatchedData(
            $patientsMatchWithInboundName->first(),
            $this->patientIsCallbackEligible(
                $patientsMatchWithInboundName->first(),
                $this->postmarkCallbackData
            ),
            $this->noCallbackReasoning($patientsMatchWithInboundName->first())
        ))
            ->getArray();
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
     * @return array
     */
    private function matchByCallerField(Collection $patientsMatchedByPhone, array $inboundPostmarkData, int $recordId)
    {
        $firstName = $this->parseNameFromCallerField($inboundPostmarkData['Clr ID'])['firstName'];
        $lastName  = $this->parseNameFromCallerField($inboundPostmarkData['Clr ID'])['lastName'];

        $patientsMatchedByCallerFieldName = $patientsMatchedByPhone
            ->where('first_name', '=', $firstName)
            ->where('last_name', '=', $lastName);

        if (0 === $patientsMatchedByCallerFieldName->count()) {
            Log::critical("Couldn't match patient for record_id:$recordId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

            return;
        }

        if ($this->singleMatch($patientsMatchedByCallerFieldName)) {
            return (new MatchedData(
                $patientsMatchedByCallerFieldName->first(),
                $this->patientIsCallbackEligible(
                    $patientsMatchedByCallerFieldName->first(),
                    $this->postmarkCallbackData
                ),
                $this->noCallbackReasoning($patientsMatchedByCallerFieldName->first())
            ))
                ->getArray();
        }

        return (new MatchedData(
            $patientsMatchedByCallerFieldName,
            false,
            self::NO_NAME_MATCH_SELF
        ))
            ->getArray();
    }

    /**
     * @return bool
     */
    private function multiMatch(Builder $postmarkInboundPatientsMatched)
    {
        return $postmarkInboundPatientsMatched->count() > 1;
    }

    /**
     *  Returns the reason why a callback can't be assigned to a $patientUser.
     *
     * @return string
     */
    private function noCallbackReasoning(Model $patientUser)
    {
        $reason = 'unmatched';
        /** @var User $patientUser */
        if ($this->isQueuedForEnrollmentAndUnassigned($patientUser)) {
            return self::QUEUED_AND_UNASSIGNED;
        }

        if ($this->requestsCancellation($this->postmarkCallbackData)) {
            return self::WITHDRAW_REQUEST;
        }

        if ( ! $this->isPatientEnrolled($patientUser)) {
            return self::NOT_ENROLLED;
        }

        return $reason;
    }

    /**
     * @return string[]
     */
    private function parseNameFromCallerField(string $callerField)
    {
        $patientNameArray = $this->parsePostmarkInboundField($callerField);

        return [
            'firstName' => isset($patientNameArray[1]) ? $patientNameArray[1] : '',
            'lastName'  => isset($patientNameArray[2]) ? $patientNameArray[2] : '',
        ];
    }

    /**
     * @return array|false|string[]
     */
    private function parsePostmarkInboundField(string $string)
    {
        return preg_split('/(?=[A-Z])/', preg_replace('/[^a-zA-Z]+/', '', $string));
    }

    /**
     * @param $patientUser
     * @return bool
     */
    private function patientIsCallbackEligible($patientUser, array $inboundPostmarkData)
    {
        return $this->isCallbackEligible($inboundPostmarkData, $patientUser);
    }

    private function singleMatch(Collection $postmarkInboundPatientsMatched)
    {
        return 1 === $postmarkInboundPatientsMatched->count();
    }
}
