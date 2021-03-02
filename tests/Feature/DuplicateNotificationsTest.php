<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Core\Notifications\Channels\DatabaseChannel;
use CircleLinkHealth\Core\Notifications\Channels\DirectMailChannel;
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
        AppConfig::set(DuplicateNotificationChecker::TYPES_CONFIG_KEY, '*');
        AppConfig::set(DuplicateNotificationChecker::MINUTES_CHECK_CONFIG_KEY, '10');
    }

    public function test_it_will_not_send_same_notification_to_user_twice()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        $this->storeInDb($provider, $note, 'mail');
        $result = DuplicateNotificationChecker::hasAlreadySentNotification($provider, $note, 'mail');
        self::assertTrue($result);
    }

    public function test_it_will_not_send_same_notification_to_user_twice_2()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        $this->storeInDb($provider, $note, DirectMailChannel::class);
        $result = DuplicateNotificationChecker::hasAlreadySentNotification($provider, $note, DirectMailChannel::class);
        self::assertTrue($result);
    }

    public function test_it_will_not_send_same_notification_to_users_of_location_twice()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        /** @var Location $location */
        $location = $provider->locations->first();
        $this->storeInDb($location, $note, 'mail');
        $result = DuplicateNotificationChecker::hasAlreadySentNotification($location, $note, 'mail');
        self::assertTrue($result);
    }

    public function test_it_will_send_notification_to_users_of_location_that_did_not_already_receive_it()
    {
        $note      = $this->getNotification();
        $providers = $this->provider(2);

        $pLocationId = $providers[0]->locations->first()->id;
        self::assertEquals($pLocationId, $providers[1]->locations->first()->id);

        $this->storeInDb($providers[0], $note, 'mail');

        $result = DuplicateNotificationChecker::hasAlreadySentNotification($providers[0], $note, 'mail');
        self::assertTrue($result);

        /** @var Location $location */
        $location = $providers[0]->locations->first();
        $result   = DuplicateNotificationChecker::hasAlreadySentNotification($location, $note, 'mail');

        self::assertFalse($result);
    }

    public function test_it_will_send_same_notification_to_user_in_another_channel()
    {
        $note     = $this->getNotification();
        $provider = $this->provider();
        $this->storeInDb($provider, $note, 'mail');
        $result = DuplicateNotificationChecker::hasAlreadySentNotification($provider, $note, DirectMailChannel::class);
        self::assertFalse($result);
    }

    private function getNotification(): NoteForwarded
    {
        $fakeNote = \factory(Note::class)->create();

        $notification     = new NoteForwarded($fakeNote, ['database', 'mail', 'twilio']);
        $notification->id = (string) Uuid::uuid();

        return $notification;
    }

    private function storeInDb($notifiable, Notification $notification, string $channel)
    {
        $method = new ReflectionMethod(DatabaseChannel::class, 'buildPayload');
        $method->setAccessible(true);
        $dbChannel                  = new DatabaseChannel();
        $payload                    = $method->invoke($dbChannel, $notifiable, $notification);
        $payload['notifiable_id']   = $notifiable->id;
        $payload['notifiable_type'] = get_class($notifiable);
        $payload['data']            = [
            'status' => [
                'database' => ['value' => 'pending'],
                $channel   => ['value' => 'pending'],
            ],
        ];

        DatabaseNotification::create($payload);
    }
}
