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

        $inboundMailDomain = ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL_DOMAIN;
        $primary           = $patient->getBillingProviderName() ?: 'Salah';
        $clrId             = $number.' '.$patient->display_name;

        $callbackMailData = "For: GROUP DISTRIBUTION
        From:| $inboundMailDomain |
        Phone:| $number |
        Ptn:| $name  |
        Primary:| $primary |
        Msg:| REQUEST TO BE REMOVED OFF ALL LISTS  |
        Msg ID: Not relevant
        IS Rec #: Not relevant
        Clr ID: $clrId
        Taken: Not relevant";

        if ($requestsToWithdraw) {
            $withdrawReasonText = 'Cancel/Withdraw Reason:| I want to Cancel |';
            $extraValues        = "\n".' '.$withdrawReasonText;
            $callbackMailData   = $callbackMailData.$extraValues;
        }

        return $callbackMailData;
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
                'data' => json_encode(
                    [
                        'From'     => 'message.dispatch@callcenterusa.net',
                        'TextBody' => $this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf),
                    ]
                ),
            ]
        );
    }

    private function createUserWithPatientCcmStatus(\CircleLinkHealth\Customer\Entities\Practice $practice, string $status)
    {
        return $this->createUser($practice->id, 'participant', $status);
    }

    private function generatePostmarkCallbackData(bool $requestToWithdraw, bool $nameIsSelf)
    {
        $this->postmarkRecord = $this->getCallbackMailData($this->patient, $requestToWithdraw, $nameIsSelf);
    }
}
