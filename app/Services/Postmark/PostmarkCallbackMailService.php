<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Traits\UserHelpers;

class PostmarkCallbackMailService
{
    use UserHelpers;

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
        $practice = Practice::whereName('demo-clinic')->firstOrFail();
        $role     = Role::whereName('participant')->firstOrFail();
        $patient  = $this->createUser($practice->id, $role->name, Patient::TO_ENROLL);

        return json_encode(
            [
                'For'      => 'GROUP DISTRIBUTION',
                'From'     => ProcessPostmarkInboundMailJob::FROM_CALLBACK_EMAIL,
                'Phone'    => $patient->phoneNumbers->first()->number,
                'Ptn'      => 'SELF',
                'Msg'      => 'voice',
                'Primary'  => $patient->getBillingProviderName() ?: 'Salah',
                'Msg ID'   => 'Not relevant',
                'IS Rec #' => 'Not relevant',
                'Clr ID'   => 'Phone number and caller ID of inbound caller in 8888888888 + (Caller ID) format',
                'Taken'    => 'Not relevant',
            ]
        );
    }

    public function parse(int $postmarkRecordId)
    {
        return $postmarkRecordId;
    }
}
