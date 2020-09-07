<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Tests;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;

trait PostmarkCallbackHelpers
{
    public function getCallbackMailData(User $patient, bool $requestsToWithdraw)
    {
        $phone = $patient->phoneNumbers->first()->number;
        $name  = $patient->display_name;

        $inboundPostmarkData = [
            'For'      => 'GROUP DISTRIBUTION',
            'From'     => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
            'Phone'    => $phone,
            'Ptn'      => $name,
            'Msg'      => '| REQUEST TO BE REMOVED OFF ALL LISTS  |',
            'Primary'  => $patient->getBillingProviderName() ?: 'Salah',
            'Msg ID'   => 'Not relevant',
            'IS Rec #' => 'Not relevant',
            'Clr ID'   => "$phone $patient->display_name",
            'Taken'    => 'Not relevant',
        ];

        if ($requestsToWithdraw) {
            $requestsToWithdrawText                        = 'I want to Cancel';
            $inboundPostmarkData['Cancel/Withdraw Reason'] = $requestsToWithdrawText;
            $inboundPostmarkData['Msg']                    = 'Im so happy yohhoo.';
        }

        return $inboundPostmarkData;
    }

    private function createEnrolledEnrollee(User $patient, int $careAmbassadorId, string $status)
    {
        return Enrollee::firstOrCreate([
            'user_id'                 => $patient->id,
            'home_phone'              => formatPhoneNumberE164($patient->phoneNumbers->first()->number),
            'status'                  => $status,
            'care_ambassador_user_id' => $careAmbassadorId,
        ]);
    }

    private function createEnrolledUser(\CircleLinkHealth\Customer\Entities\Practice $practice, string $status)
    {
        $status = Patient::TO_ENROLL;

        return $this->createUser($practice->id, 'participant', $status);
    }

    private function sendNotification(User $patient, bool $requestsToWithdraw)
    {
        return '';
        //        Fake a notification here.
    }

    private function setUpTest(string $status, bool $requestToWithdraw = false)
    {
        $this->practice        = $this->setupPractice();
        $this->careAmbassador  = $this->createUser($this->practice->id, 'care-ambassador');
        $this->patient         = $this->createEnrolledUser($this->practice, $status);
        $this->patientEnrollee = $this->createEnrolledEnrollee($this->patient, $this->careAmbassador->id, $status);
        $this->postmarkRecord  = $this->sendNotification($this->patient, $requestToWithdraw);
        $r                     = $this->patient;
        $x                     = 1;
    }
}
