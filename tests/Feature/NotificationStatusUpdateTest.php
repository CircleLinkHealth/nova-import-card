<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\NotificationStatusUpdateJob;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class NotificationStatusUpdateTest extends CustomerTestCase
{
    public function test_it_update_notification_status_in_db_from_notification_sent_event()
    {
        // 1. create notification
        $patient = $this->patient();

        //id field has 36 length in db
        $id = Str::substr('test-'.Str::uuid()->toString(), 0, 36);

        $notification     = new SelfEnrollmentInviteNotification('http://test?123', false, []);
        $notification->id = $id;
        $patient->notify($notification);

        // 2. assert that notification status is updated with status pending
        $dbRecord = DatabaseNotification::findOrFail($id);

        self::assertEquals('pending', $dbRecord->data['status']['database']['value']);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_it_updates_notification_status_in_db()
    {
        // 1. create notification
        $patient = $this->patient();
        $patient->clearAllPhonesAndAddNewPrimary('+12025550193', 'mobile', true);

        //id field has 36 length in db
        $id = Str::substr('test-'.Str::uuid()->toString(), 0, 36);

        $notification     = new SelfEnrollmentInviteNotification('http://test?123', false, ['twilio']);
        $notification->id = $id;
        $patient->notify($notification);

        // 2. assert that notification status is updated
        $dbRecord = DatabaseNotification::findOrFail($id);

        self::assertEquals('pending', $dbRecord->data['status']['twilio']['value']);
    }

    public function test_it_updates_notification_status_in_db_from_notification_failed_event()
    {
        Twilio::fake();

        // 1. create notification
        $patient = $this->patient();
        $patient->clearAllPhonesAndAddNewPrimary('+12025550193', 'mobile', true);

        //id field has 36 length in db
        $id = Str::substr('test-'.Str::uuid()->toString(), 0, 36);

        $notification     = new SelfEnrollmentInviteNotification('http://test?123', false, ['twilio']);
        $notification->id = $id;
        $patient->notify($notification);

        // 2. simulate a failure (call status update route from twilio)
        NotificationStatusUpdateJob::dispatchNow($id, 'twilio', [
            'value' => 'failed',
        ]);

        // 3. assert that notification status is updated with error and message and correct channel
        $dbRecord = DatabaseNotification::findOrFail($id);

        self::assertEquals('failed', $dbRecord->data['status']['twilio']['value']);
    }
}
