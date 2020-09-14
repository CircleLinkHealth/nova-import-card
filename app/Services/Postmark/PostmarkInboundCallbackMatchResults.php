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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkInboundCallbackMatchResults
{
    /**
     * @var false
     */
    private bool $isQueuedAndUnassigned;
    private bool $patientIsEnrolled;
    private array $postmarkCallbackData;
    private int $recordId;
    private bool $requestedToWithdraw;

    /**
     * PostmarkInboundCallbackMatchResults constructor.
     */
    public function __construct(array $postmarkCallbackData, int $recordId)
    {
        $this->postmarkCallbackData = $postmarkCallbackData;
        $this->recordId             = $recordId;
    }

    /**
     * @return array|Builder|void
     */
    public function getMatchedPatients()
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
                $this->noCallbackReasoning()
            ))
                ->getArray();
        }

        if ($postmarkInboundPatientsMatched->count() > 1) {
            return  $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched->get(), $this->postmarkCallbackData);
        }

        Log::warning("Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
        sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
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
        $this->patientIsEnrolled = Patient::ENROLLED === $patientUser->enrollee->status
            && Patient::ENROLLED                     === $patientUser->patientInfo->ccm_status;

        return $this->patientIsEnrolled;
    }

    /**
     * @return bool
     */
    public function isQueuedForEnrollmentAndUnassigned(User $patientUser)
    {
        if ( ! $patientUser->enrollee->exists()) {
            $this->isQueuedAndUnassigned = false;
        }

        $this->isQueuedAndUnassigned = Enrollee::QUEUE_AUTO_ENROLLMENT === $patientUser->enrollee->status
            && is_null($patientUser->enrollee->care_ambassador_user_id);

        return $this->isQueuedAndUnassigned;
    }

    /**
     * @param $postmarkData
     * @return bool
     */
    public function requestsCancellation($postmarkData)
    {
        $this->requestedToWithdraw = isset($postmarkData['Cancel/Withdraw Reason'])
            || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);

        return $this->requestedToWithdraw;
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

        if ($patientsMatchWithInboundName->isEmpty()) {
            Log::info("Postmark inbound callback with record id:$this->recordId was matched with phone but failed to match with user name");

            return (new MatchedData(
                $patientsMatchedByPhone,
                false
            ))
                ->getArray();
        }

        if (1 === $patientsMatchWithInboundName->count()) {
            return (new MatchedData(
                $patientsMatchWithInboundName->first(),
                $this->patientIsCallbackEligible(
                    $patientsMatchWithInboundName->first(),
                    $this->postmarkCallbackData
                ),
                $this->noCallbackReasoning()
            ))
                ->getArray();
        }

        return (new MatchedData(
            $patientsMatchWithInboundName,
            false,
            ['unmatched']
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
                $this->noCallbackReasoning()
            ))
                ->getArray();
        }

        return (new MatchedData(
            $patientsMatchedByCallerFieldName,
            false,
            ['no_name_match']
        ))
            ->getArray();
    }

    private function noCallbackReasoning()
    {
        $reasons = collect([]);

        if ( ! isset($this->patientIsEnrolled)
            || ! isset($this->isQueuedAndUnassigned)
            || ! isset($this->requestedToWithdraw)) {
            return $reasons->toArray();
        }

        if ( ! $this->patientIsEnrolled) {
            $reasons->push('not_enrolled');
        }

        if ($this->isQueuedAndUnassigned) {
            $reasons->push('queue_auto_enrollment_and_unassigned');
        }

        if ($this->requestedToWithdraw) {
            $reasons->push('withdraw_request');
        }

        return $reasons->toArray();
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
