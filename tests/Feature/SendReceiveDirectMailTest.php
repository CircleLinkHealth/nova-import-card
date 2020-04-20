<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Console\Commands\SendTestDirectMail;
use App\DirectMailMessage;
use App\Services\PhiMail\SendResult;
use Tests\TestCase;

class SendReceiveDirectMailTest extends TestCase
{
    public function test_it_sends_and_receives_dm_with_attachments()
    {
        //Uncomment after we figure out a secure way to give S3 and EMR Sandbox access to CI
//        $this->receiveAndAssertReceived($this->sendAndAssertSent());
    }

    private function receiveAndAssertReceived(SendResult $sent)
    {
        sleep(5);

        \Artisan::call('emrDirect:checkInbox');

        $this->assertDatabaseHas(
            'direct_mail_messages',
            [
                'message_id'      => $sent->messageId,
                'from'            => SendTestDirectMail::CLH_SANDBOX_DM_EMAIL,
                'to'              => $sent->recipient,
                'subject'         => 'phiMail Test Message: '.SendTestDirectMail::TEST_DM_SUBJECT,
                'body'            => SendTestDirectMail::TEST_DM_BODY,
                'num_attachments' => SendTestDirectMail::TEST_DM_NUM_ATTACHMENTS,
                'direction'       => DirectMailMessage::DIRECTION_RECEIVED,
                'status'          => DirectMailMessage::STATUS_SUCCESS,
            ]
        );

        $dm = DirectMailMessage::where('message_id', $sent->messageId)->firstOrFail();

        $this->assertDatabaseHas(
            'media',
            [
                'model_id'   => $dm->id,
                'model_type' => DirectMailMessage::class,
            ]
        );
    }

    private function sendAndAssertSent()
    {
        $sent = app(SendTestDirectMail::class)->sendTestDM(SendTestDirectMail::CLH_SANDBOX_DM_EMAIL);

        $this->assertIsArray($sent);
        $this->assertArrayHasKey(0, $sent);

        // @var SendResult $sent
        return $sent[0];
//        $this->assertDatabaseHas('direct_mail_messages', [
//            'message_id' => $sent->messageId,
//            'from' => SendTestDirectMail::CLH_SANDBOX_DM_EMAIL,
//            'to' => $sent->recipient,
//            'subject' => SendTestDirectMail::TEST_DM_SUBJECT,
//            'body' => SendTestDirectMail::TEST_DM_BODY,
//            'num_attachments' => SendTestDirectMail::TEST_DM_NUM_ATTACHMENTS,
//        ]);
    }
}
