<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

trait PostmarkCallbackHelpers
{
    public function getCallbackMailData(User $patient, bool $requestsToWithdraw, bool $nameIsSelf = false)
    {
        $this->phone = $patient->phoneNumbers->first();
        $number      = $this->phone->number;
        $name        = $nameIsSelf ? 'SELF' : $patient->display_name;

        $inboundPostmarkData = [
            'For'      => 'GROUP DISTRIBUTION',
            'From'     => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL_DOMAIN,
            'Phone'    => $number,
            'Ptn'      => $name,
            'Msg'      => '| REQUEST TO BE REMOVED OFF ALL LISTS  |',
            'Primary'  => $patient->getBillingProviderName() ?: 'Salah',
            'Msg ID'   => 'Not relevant',
            'IS Rec #' => 'Not relevant',
            'Clr ID'   => "$number $patient->display_name",
            'Taken'    => 'Not relevant',
        ];

        if ($requestsToWithdraw) {
            $requestsToWithdrawText                        = 'I want to Cancel';
            $inboundPostmarkData['Cancel/Withdraw Reason'] = $requestsToWithdrawText;
            $inboundPostmarkData['Msg']                    = 'Im so happy yohhoo.';
        }

        return $inboundPostmarkData;
    }

    private function createEnrolleeWithStatus(User $patient, int $careAmbassadorId, string $status)
    {
        return Enrollee::firstOrCreate([
            'user_id'                 => $patient->id,
            'home_phone'              => formatPhoneNumberE164($patient->phoneNumbers->first()->number),
            'status'                  => $status,
            'care_ambassador_user_id' => $careAmbassadorId,
        ]);
    }

    private function createPatientData(string $status)
    {
        $this->patient         = $this->createUserWithPatientCcmStatus($this->practice, $status);
        $this->patientEnrollee = $this->createEnrolleeWithStatus($this->patient, $this->careAmbassador->id, $status);
    }

    private function createPostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf)
    {
        return  PostmarkInboundMail::create(
            [
                //                saving to body will throw SQLSTATE[HY000]: General error: 3105
                // The value specified for generated column 'body' in table 'postmark_inbound_mail' is not allowed.
                'body' => json_encode($this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf)),
            ]
        );
    }

    private function createUserWithPatientCcmStatus(\CircleLinkHealth\Customer\Entities\Practice $practice, string $status)
    {
        return $this->createUser($practice->id, 'participant', $status);
    }

    private function generatePostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf)
    {
        $this->postmarkRecord = json_encode($this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf));
    }
}
