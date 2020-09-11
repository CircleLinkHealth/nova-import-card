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
            'From'     => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
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

    private function createPatientData(string $status, bool $requestToWithdraw = false, bool $nameIsSelf = false)
    {
        $this->patient         = $this->createUserWithPatientCcmStatus($this->practice, $status);
        $this->patientEnrollee = $this->createEnrolleeWithStatus($this->patient, $this->careAmbassador->id, $status);
        $this->postmarkRecord  = $this->createPostmarkCallbackNotification($this->patient, $requestToWithdraw, $nameIsSelf);
    }

    private function createPostmarkCallbackNotification(User $patient, bool $requestToWithdraw, bool $nameIsSelf)
    {
        return  PostmarkInboundMail::create(
            [
                'from' => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
                'data' => json_encode($this->getCallbackMailData($patient, $requestToWithdraw, $nameIsSelf)),
                'body' => 'This is it',
            ]
        );
    }

    private function createUserWithPatientCcmStatus(\CircleLinkHealth\Customer\Entities\Practice $practice, string $status)
    {
        return $this->createUser($practice->id, 'participant', $status);
    }
}
