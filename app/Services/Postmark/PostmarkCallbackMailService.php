<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkCallbackMailService
{
    /**
     * @return array|Builder|\Collection|Collection|Model|object|void|null
     */
    public function getMatchedPatient(array $inboundPostmarkData, int $recId)
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($inboundPostmarkData);

        $patientMatch = collect([]);
        if (1 === $postmarkInboundPatientsMatched->count()) {
            $patientMatch = $this->createCallbackIfEligible($postmarkInboundPatientsMatched->first(), $inboundPostmarkData);
        }

        if ($postmarkInboundPatientsMatched->count() > 1) {
            $patientMatch = $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched, $inboundPostmarkData);
        }

        if ( ! $patientMatch) {
            Log::warning("Could not find a patient match for record_id:[$recId] in postmark_inbound_mail");

            return;
        }

        return [
            'patient'     => $patientMatch,
            'phoneNumber' => $inboundPostmarkData['Phone'],
        ];
    }

    /**
     * @return mixed|void
     */
    public function parsedEmailData(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        return json_decode($postmarkRecord->data);
    }

    /**
     * @param $patientUser
     * @return Collection
     */
    private function createCallbackIfEligible($patientUser, array $inboundPostmarkData)
    {
        if ( ! $this->isPatientEnrolled($patientUser)
            || $this->isQueuedForEnrollmentAndUnassigned($patientUser)
            || $this->requestsCancellation($inboundPostmarkData)) {
            return Collection::make();
        }

        return $patientUser;
    }

    private function filterPostmarkInboundPatientsByName(Builder $patientsMatchedByPhone, array $inboundPostmarkData)
    {
        if ('SELF' === $inboundPostmarkData['Ptn']) {
            return $this->matchByCallerField($patientsMatchedByPhone, $inboundPostmarkData);
        }

        $usersMatchWithInboundName = $patientsMatchedByPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);

        if (0 === $usersMatchWithInboundName->count()) {
            $recId = $inboundPostmarkData['id'];
            Log::critical("Cannot match postmark inbound data with our records for record_id $recId");
            sendSlackMessage('#carecoach_ops_alerts', "Could not match inbound mail with a patient from our records:[$recId] in postmark_inbound_mail");

            return;
        }

        if (1 === $usersMatchWithInboundName->count()) {
            return $usersMatchWithInboundName->first();
        }

        return collect([]);
    }

    private function getPostmarkInboundPatientsByPhone(array $inboundPostmarkData)
    {
        return User::ofType('participant')
            ->with('patientInfo', 'enrollee', 'phoneNumbers') //Get only what you need from each relationship mate.
            ->whereHas('phoneNumbers', function ($phoneNumber) use ($inboundPostmarkData) {
                $phoneNumber->where('number', $inboundPostmarkData['Phone']);
            });
    }

    private function isPatientEnrolled(User $patientUser)
    {
        return Patient::ENROLLED === $patientUser->enrollee->status
            && Patient::ENROLLED === $patientUser->patientInfo->ccm_status;
    }

    private function isQueuedForEnrollmentAndUnassigned(User $patientUser)
    {
        if ( ! $patientUser->enrollee->exists()) {
            return false;
        }

        return Enrollee::QUEUE_AUTO_ENROLLMENT === $patientUser->enrollee->status
            && is_null($patientUser->enrollee->care_ambassador_user_id);
    }

    private function matchByCallerField(Builder $patientsMatchedByPhone, array $inboundPostmarkData)
    {
//        $callerFieldPhone = $this->parsePhoneFromCallerField($inboundPostmarkData['Clr ID']);
        $firstName = $this->parseNameFromCallerField($inboundPostmarkData['Clr ID'])['firstName'];
        $lastName  = $this->parseNameFromCallerField($inboundPostmarkData['Clr ID'])['lastName'];

        $patientsMatchedByCallerFieldName = $patientsMatchedByPhone
            ->where('first_name', '=', $firstName)
            ->where('last_name', '=', $lastName);

        if (0 === $patientsMatchedByCallerFieldName->count()) {
            $recId = $inboundPostmarkData['id'];
            Log::critical("Couldn't match patient for record_id:$recId in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recId] in postmark_inbound_mail");

            return;
        }

        if (1 === $patientsMatchedByCallerFieldName->count()) {
            return $patientsMatchedByCallerFieldName->first();
        }

        return collect([]);
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

    private function parsePhoneFromCallerField(string $callerField)
    {
        return intval(preg_replace('/[^0-9]+/', '', $callerField), 10);
    }

    private function parsePostmarkInboundField(string $string)
    {
        return preg_split('/(?=[A-Z])/', preg_replace('/[^a-zA-Z]+/', '', $string));
    }

    private function requestsCancellation($postmarkData)
    {
        return isset($postmarkData['Cancel/Withdraw Reason'])
        || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }
}
