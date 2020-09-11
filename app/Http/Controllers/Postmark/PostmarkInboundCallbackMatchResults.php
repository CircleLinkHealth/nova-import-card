<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Postmark;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkInboundCallbackMatchResults extends Controller
{
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
     * @return array|Builder|void
     */
    public function getMatchedPatients()
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($this->postmarkCallbackData);

        if ($this->singleMatch($postmarkInboundPatientsMatched)) {
            return [
                'matchResult'    => $postmarkInboundPatientsMatched->first(),
                'createCallback' => $this->patientIsCallbackEligible(
                    $postmarkInboundPatientsMatched->first(),
                    $this->postmarkCallbackData
                ),
            ];
        }

        if ($postmarkInboundPatientsMatched->count() > 1) {
            return  $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched, $this->postmarkCallbackData);
        }

        Log::warning("Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
        sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$this->recordId] in postmark_inbound_mail");
    }

    /**
     * @return array|Builder|void
     */
    private function filterPostmarkInboundPatientsByName(Builder $patientsMatchedByPhone, array $inboundPostmarkData)
    {
        if ('SELF' === $inboundPostmarkData['Ptn']) {
            return $this->matchByCallerField($patientsMatchedByPhone, $inboundPostmarkData, $this->recordId);
        }

        $patientsMatchWithInboundName = $patientsMatchedByPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);

        if (0 === $patientsMatchWithInboundName->count()) {
            Log::critical("Cannot match postmark inbound data with our records for record_id $this->recordId");
            sendSlackMessage('#carecoach_ops_alerts', "Could not match inbound mail with a patient from our records:[$this->recordId] in postmark_inbound_mail");

            return;
        }

        if ($this->singleMatch($patientsMatchWithInboundName)) {
            return [
                'matchResult'    => $patientsMatchWithInboundName->first(),
                'createCallback' => $this->patientIsCallbackEligible(
                    $patientsMatchWithInboundName->first(),
                    $inboundPostmarkData
                ),
            ];
        }

        return [
            'matchResult'    => $patientsMatchWithInboundName->get(),
            'createCallback' => false,
        ];
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
     * @return bool
     */
    private function isPatientEnrolled(User $patientUser)
    {
        return Patient::ENROLLED === $patientUser->enrollee->status
            && Patient::ENROLLED === $patientUser->patientInfo->ccm_status;
    }

    /**
     * @return bool
     */
    private function isQueuedForEnrollmentAndUnassigned(User $patientUser)
    {
        if ( ! $patientUser->enrollee->exists()) {
            return false;
        }

        return Enrollee::QUEUE_AUTO_ENROLLMENT === $patientUser->enrollee->status
            && is_null($patientUser->enrollee->care_ambassador_user_id);
    }

    /**
     * @return array
     */
    private function matchByCallerField(Builder $patientsMatchedByPhone, array $inboundPostmarkData, int $recordId)
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
            return [
                'matchResult'    => $patientsMatchedByCallerFieldName->first(),
                'createCallback' => $this->patientIsCallbackEligible(
                    $patientsMatchedByCallerFieldName->first(),
                    $inboundPostmarkData
                ),
            ];
        }

        return [
            'matchResult'    => $patientsMatchedByCallerFieldName->get(),
            'createCallback' => false,
        ];
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
        if ( ! $this->isPatientEnrolled($patientUser)
            || $this->isQueuedForEnrollmentAndUnassigned($patientUser)
            || $this->requestsCancellation($inboundPostmarkData)) {
            return false;
        }

        return true;
    }

    /**
     * @param $postmarkData
     * @return bool
     */
    private function requestsCancellation($postmarkData)
    {
        return isset($postmarkData['Cancel/Withdraw Reason'])
            || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }

    private function singleMatch(Builder $postmarkInboundPatientsMatched)
    {
        return 1 === $postmarkInboundPatientsMatched->count();
    }
}
