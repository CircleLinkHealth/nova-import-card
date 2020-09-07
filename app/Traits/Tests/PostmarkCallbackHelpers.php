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
    public function getCallbackMailData(User $patient, bool $requestsToWithdraw)
    {
        $phone                  = $patient->phoneNumbers->first()->number;
        $name                   = $patient->display_name;
        $requestsToWithdrawText = '';
        
        if ($requestsToWithdraw) {
            $requestsToWithdrawText = 'I want to Cancel';
        }
        
        return json_encode(
            array_merge($this->constantFields($patient, $phone, $name), [
                'Cancel/Withdraw Reason' => $requestsToWithdrawText,
            ])
        );
    }
    
    private function constantFields(User $patient, $phone, string $name)
    {
        return [
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
        return $this->createUser($practice->id, 'participant', $status);
    }
    
    private function setUpPostmarkRecord(User $patient, bool $requestsToWithdraw = false)
    {
        return  PostmarkInboundMail::firstOrCreate(
            [
                'from' => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
            ],
            [
                'data' => $this->getCallbackMailData($patient, $requestsToWithdraw),
                'body' => 'This is a sexy text body',
            ]
        );
    }
    
    private function setUpTest(string $status)
    {
        $this->practice        = $this->setupPractice();
        $this->careAmbassador  = $this->createUser($this->practice->id, 'care-ambassador');
        $this->patient         = $this->createEnrolledUser($this->practice, $status);
        $this->patientEnrollee = $this->createEnrolledEnrollee($this->patient, $this->careAmbassador->id, $status);
        $this->postmarkRecord  = $this->setUpPostmarkRecord($this->patient);
    }
}
