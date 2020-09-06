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

    public function decideAction(array $inboundPostmarkData)
    {
        /** @var User $postmarkInboundPatients */
        $postmarkInboundPatients = User::ofType('participant')
            ->with('patientInfo', 'enrollee', 'phoneNumbers')
            ->where(function ($query) use ($inboundPostmarkData) {
                $query->whereHas('phoneNumbers', function ($phoneNumber) use ($inboundPostmarkData) {
                    $phoneNumber->where('number', $inboundPostmarkData['Phone']);
                });
            });

        if (1 === $postmarkInboundPatients->count()) {
            $this->managePostmarkInboundNotification($postmarkInboundPatients->first(), $inboundPostmarkData);
        }

        if ($postmarkInboundPatients->count() > 1) {
//            Check by name ...
            $users = $postmarkInboundPatients->get();
            foreach ($users as $user) {
                //        - If two patients with same phone -> Match by name and Clr Id
                //        - If name is SELF match by FROM / Caller id...
            }
        }

//        2. Return $user,textBody,phoneNumber

        return [
        ];
    }

    public function getCallbackMailData()
    {
        //        This is Temporary
        $patient = User::with('enrollee')->whereHas('enrollee', function ($enrollee) {
            $enrollee->where('status', '=', Patient::ENROLLED);
        })->firstOrFail();

        return json_encode(
            [
                'For'   => 'GROUP DISTRIBUTION',
                'From'  => 'Ethan Roney',
                'Phone' => $patient->phoneNumbers->first()->number,
                'Ptn'   => $patient->display_name,
                //                'Cancel/Withdraw Reason' => "| PTN EXPIRED  |",
                'Msg'      => '| REQUEST TO BE REMOVED OFF ALL LISTS  |',
                'Primary'  => $patient->getBillingProviderName() ?: 'Salah',
                'Msg ID'   => 'Not relevant',
                'IS Rec #' => 'Not relevant',
                'Clr ID'   => 'Phone number and caller ID of inbound caller in 8888888888 + (Caller ID) format',
                'Taken'    => 'Not relevant',
            ]
        );
    }

    /**
     * @return array|\Collection|\Illuminate\Support\Collection|void
     */
    public function parsedEmail(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        return json_decode($postmarkRecord->data);
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

    private function managePostmarkInboundNotification(User $patientUser, array $inboundPostmarkData)
    {
        if ($this->isPatientEnrolled($patientUser)) {
            //             Create callback.
        }

        if ($this->isQueuedForEnrollmentAndUnassigned($patientUser)
            || isset($inboundPostmarkData['Cancel/Withdraw Reason'])
            || $this->requestsCancellation($inboundPostmarkData['Msg'])) {
            //             Assign to CA's.
        }
    }

    private function requestsCancellation($message)
    {
        return Str::contains(Str::of($message)->upper(), ['CANCEL', 'CX', 'WITHDRAW']);
    }
}
