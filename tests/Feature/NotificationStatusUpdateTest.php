<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Jobs\NotificationStatusUpdateJob;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Facades\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
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

        //id field has 36 length in db
        $id = Str::substr('test-'.Str::uuid()->toString(), 0, 36);

        $notification     = new SelfEnrollmentInviteNotification('http://test?123', false, []);
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

    public function test_it_updates_notification_status_in_db_from_notification_failed_event()
    {
        // 1. make sure twilio creds are invalid
        $key1    = 'services.twilio.enabled';
        $config1 = config($key1);
        Config::set($key1, true);

        $key2    = 'services.twilio.account_sid';
        $config2 = config($key2);
        Config::set($key2, 'somestring');

        $key3    = 'services.twilio.auth_token';
        $config3 = config($key3);
        Config::set($key3, 'somestring');

        $key4    = 'services.twilio.twiml-app-sid';
        $config4 = config($key4);
        Config::set($key4, 'somestring');

        // 2. create notification
        $patient = $this->patient();

        //id field has 36 length in db
        $id = Str::substr('test-'.Str::uuid()->toString(), 0, 36);

        try {
            $notification     = new SelfEnrollmentInviteNotification('http://test?123', false, [CustomTwilioChannel::class]);
            $notification->id = $id;
            $patient->notify($notification);
        } catch (\Exception $e) {
            //should throw exception because queue is sync
        }

        // 3. assert that notification status is updated with error and message and correct channel
        $dbRecord = DatabaseNotification::findOrFail($id);

        self::assertEquals('failed', $dbRecord->data['status']['twilio']['value']);

        // 4. reset config
        Config::set($key1, $config1);
        Config::set($key2, $config2);
        Config::set($key3, $config3);
        Config::set($key4, $config4);
    }
}
