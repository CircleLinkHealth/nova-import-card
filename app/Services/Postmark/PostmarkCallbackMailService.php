<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkCallbackMailService
{
    // Temporary function. Helping development.
    public function createCallbackNotification()
    {
        PostmarkInboundMail::firstOrCreate(
            [
                'from' => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
            ],
            [
                'data' => $this->getCallbackMailData(),
                'body' => 'This is a sexy text body',
            ]
        );
    }

    public function getCallbackMailData()
    {
        //        This is Temporary
        $patient = User::with('enrollee')->whereHas('enrollee', function ($enrollee) {
            $enrollee->where('status', '=', Patient::ENROLLED);
        })->firstOrFail();

        $phone = $patient->phoneNumbers->first()->number;

        return json_encode(
            [
                'For'   => 'GROUP DISTRIBUTION',
                'From'  => 'Ethan Roney',
                'Phone' => $phone,
                'Ptn'   => $patient->display_name,
                //                'Cancel/Withdraw Reason' => "| PTN EXPIRED  |",
                'Msg'      => '| REQUEST TO BE REMOVED OFF ALL LISTS  |',
                'Primary'  => $patient->getBillingProviderName() ?: 'Salah',
                'Msg ID'   => 'Not relevant',
                'IS Rec #' => 'Not relevant',
                'Clr ID'   => "$phone $patient->display_name",
                'Taken'    => 'Not relevant',
            ]
        );
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

    public function shouldCreateCallBack(array $inboundPostmarkData)
    {
        /** @var Builder $postmarkInboundPatientsMatched */
        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($inboundPostmarkData);

        if (1 === $postmarkInboundPatientsMatched->count()) {
            $this->createCallbackIfEligible($postmarkInboundPatientsMatched->first(), $inboundPostmarkData);
        }

        if ($postmarkInboundPatientsMatched->count() > 1) {
            $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched, $inboundPostmarkData);
        }

//        2. Return $user,textBody,phoneNumber

        return [
        ];
    }

    private function createCallbackIfEligible($patientUser, array $inboundPostmarkData)
    {
        if ( ! $this->isPatientEnrolled($patientUser)
            || $this->isQueuedForEnrollmentAndUnassigned($patientUser)
            || $this->requestsCancellation($inboundPostmarkData)) {
            //             Assign to CA's.
        }

        //         Create Callback
    }

    private function filterPostmarkInboundPatientsByName(Builder $patientsMatchedByPhone, array $inboundPostmarkData)
    {
        if ('SELF' === $inboundPostmarkData['Ptn']) {
            $this->matchByCallerIdAndFromField($patientsMatchedByPhone, $inboundPostmarkData);
        }

        //        - Match by name and Clr Id ALSO***
        $usersMatchWithInboundName = $patientsMatchedByPhone->where('display_name', '=', $inboundPostmarkData['Ptn']);

        if (0 === $usersMatchWithInboundName->count()) {
            $recId = $inboundPostmarkData['id'];
            Log::critical("Cannot match postmark inbound data with our records for record_id $recId");
            sendSlackMessage('#carecoach_ops_alerts', "Could not matach inbound mail with a patient from our records:[$recId] in postmark_inbound_mail");

            return;
        }

        if (1 === $usersMatchWithInboundName->count()) {
            return $usersMatchWithInboundName->first();
        }
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

    private function matchByCallerIdAndFromField(Builder $patientsMatchedByPhone, array $inboundPostmarkData)
    {
        /** @var User $x */
        $x = $patientsMatchedByPhone->get();
//        1. Check if User name is equal to Clr name - NOT Strict comparison
//        2. Check by phone of CLrId
//        3. Check by name compared to FROM field (this might be a relative) - - NOT Strict comparison
    
    }

    private function parsePhoneFromClrId(string $clrId)
    {
        return intval(preg_replace('/[^0-9]+/', '', $clrId), 10);
    }

    private function requestsCancellation($postmarkData)
    {
        return isset($postmarkData['Cancel/Withdraw Reason'])
        || Str::contains(Str::of($postmarkData['Msg'])->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }
}
