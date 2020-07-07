<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Contracts\Efax;
use Tests\TestCase;

class PhaxioServiceTest extends TestCase
{
    public function fakeWebhookResponse()
    {
        $faxObject = [
            'id'           => 123456,
            'direction'    => 'sent',
            'num_pages'    => 3,
            'status'       => 'success',
            'is_test'      => true,
            'created_at'   => '2015-09-02T11:28:02.000-05:00',
            'caller_id'    => '+18476661235',
            'from_number'  => null,
            'completed_at' => '2015-09-02T11:28:54.000-05:00',
            'caller_name'  => 'Catherine Lee',
            'cost'         => 21,
            'tags'         => [
                'order_id' => '1234',
            ],
            'recipients' => [
                [
                    'phone_number'  => '+14141234567',
                    'status'        => 'success',
                    'retry_count'   => 0,
                    'completed_at'  => '2015-09-02T11:28:54.000-05:00',
                    'bitrate'       => 14400,
                    'resolution'    => 8040,
                    'error_type'    => null,
                    'error_id'      => null,
                    'error_message' => null,
                ],
            ],
            'to_number'     => null,
            'error_id'      => null,
            'error_type'    => null,
            'error_message' => null,
            'barcodes'      => [
            ],
        ];

        return [
            'fax'        => json_encode($faxObject),
            'event_type' => 'fax_completed',
        ];
    }

    public function test_fax_completed_webhook()
    {
        $response = $this->post(route('webhook.on-fax-sent'), $this->fakeWebhookResponse());

        $response->assertStatus(200);
    }

    public function test_it_does_not_send_fax_without_to()
    {
        $exception = false;
        try {
            $this->getService()->send(
                ['file' => storage_path('pdfs/careplans/sample-careplan.pdf')]
            );
        } catch (\InvalidArgumentException $e) {
            $exception = true;
        }
        $this->assertTrue($exception);
    }
}
