<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use Illuminate\Support\Facades\Log;

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

        //        This is Temporary
        $patient = User::with('enrollee')->whereHas('enrollee', function ($enrollee) {
            $enrollee->where('status', '=', Patient::ENROLLED);
        })->firstOrFail();
        

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

    /**
     * @return array|void
     */
    public function parseEmail(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        $callbackData = json_decode($postmarkRecord->data);

        return [
            'patientPhone' => $callbackData->Phone,
            'callerId'     => $callbackData->Phone.' '.'Get Caller id',
            'patientName'  => $callbackData->Ptn,
        ];
    }
}
