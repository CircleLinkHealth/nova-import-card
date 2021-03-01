<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel;
use CircleLinkHealth\Core\Notifications\DuplicateNotificationChecker;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Notifications\NoteForwarded;
use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\SharedModels\Entities\Note;
use Faker\Provider\Uuid;
use Illuminate\Notifications\Notification;
use ReflectionMethod;

class DuplicateNotificationsTest extends CustomerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AppConfig::set(DuplicateNotificationChecker::CONFIG_KEY, '*');
    }

    public function test_it_will_not_send_same_notification_to_user_twice()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        $this->storeInDb($provider, $note);
        $result = DuplicateNotificationChecker::hasAlreadySentNotification($provider, $note);
        self::assertTrue($result);
    }

    public function test_it_will_not_send_same_notification_to_users_of_location_twice()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        /** @var Location $location */
        $location = $provider->locations->first();
        $this->storeInDb($location, $note);

        $result = DuplicateNotificationChecker::hasAlreadySentNotification($location, $note);
        self::assertTrue($result);
    }

    public function test_it_will_send_notification_to_users_of_location_that_did_not_already_receive_it()
    {
        $note      = $this->getNotification();
        $providers = $this->provider(2);

        $pLocationId = $providers[0]->locations->first()->id;
        self::assertEquals($pLocationId, $providers[1]->locations->first()->id);

        $this->storeInDb($providers[0], $note);

        $result = DuplicateNotificationChecker::hasAlreadySentNotification($providers[0], $note);
        self::assertTrue($result);

        /** @var Location $location */
        $location = $providers[0]->locations->first();
        $result   = DuplicateNotificationChecker::hasAlreadySentNotification($location, $note);

        self::assertFalse($result);
    }

    private function getNotification(): NoteForwarded
    {
        $fakeNote = \factory(Note::class)->create();

        $notification     = new NoteForwarded($fakeNote, ['database', 'mail', 'twilio']);
        $notification->id = (string) Uuid::uuid();

        return $notification;
    }

    private function storeInDb($notifiable, Notification $notification)
    {
        $method = new ReflectionMethod(DatabaseChannel::class, 'buildPayload');
        $method->setAccessible(true);
        $channel                    = new DatabaseChannel();
        $payload                    = $method->invoke($channel, $notifiable, $notification);
        $payload['notifiable_id']   = $notifiable->id;
        $payload['notifiable_type'] = get_class($notifiable);

        DatabaseNotification::create($payload);
    }
}
