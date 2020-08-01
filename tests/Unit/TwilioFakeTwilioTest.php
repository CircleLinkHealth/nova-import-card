<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Notifications\SendSms;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use NotificationChannels\Twilio\TwilioSmsMessage;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\Concerns\TwilioFake\WithTwilioMock;
use Tests\CustomerTestCase;

class TwilioFakeTest extends CustomerTestCase
{
    use WithTwilioMock;

    public function test_assert_nothing_sent()
    {
        $msg        = 'fake message';
        $to         = '+12349010035';
        $numberSent = 0;

        for ($i = 0; $i < $numberSent; ++$i) {
            $this->twilio()->sendMessage(
                (new TwilioSmsMessage())
                    ->content($msg),
                $to
            );
        }

        try {
            $wrongNumberSent = $numberSent + 1;
            $this->twilio()->assertNumberOfMessagesSent($wrongNumberSent);
        } catch (ExpectationFailedException $e) {
            $expected = "Failed to send [$wrongNumberSent] SMS messages. [$numberSent] were sent.\nFailed asserting that false is true.";

            $this->assertTrue($expected === $e->getMessage(), "Failed asserting that expected `$expected` equals `{$e->getMessage()}`");
        }

        $this->twilio()->assertNothingSent();
    }

    public function test_assert_number_sent_counts_correctly()
    {
        $msg        = 'fake message';
        $to         = '+12349010035';
        $numberSent = 5;

        for ($i = 0; $i < $numberSent; ++$i) {
            $this->twilio()->sendMessage(
                (new TwilioSmsMessage())
                    ->content($msg),
                $to
            );
        }

        try {
            $wrongNumberSent = $numberSent - 1;
            $this->twilio()->assertNumberOfMessagesSent($wrongNumberSent);
        } catch (ExpectationFailedException $e) {
            $expected = "Failed to send [$wrongNumberSent] SMS messages. [$numberSent] were sent.\nFailed asserting that false is true.";

            $this->assertTrue($expected === $e->getMessage(), "Failed asserting that expected `$expected` equals `{$e->getMessage()}`");
        }

        $this->twilio()->assertNumberOfMessagesSent($numberSent);
    }

    public function test_fake_message_is_not_sent_if_notification_does_not_have_twilio_channel()
    {
        $msg = 'fake message from mars';
        $to  = '+12349010035';

        NotificationFacade::fake();
        Twilio::fake();

        /** @var AnonymousNotifiable $anonymous */
        $anonymous = NotificationFacade::route('twilio', $to);
        $anonymous->route('mail', 'test@test.com');
        $anonymous->notify(new FakeTwilioNotification($msg, ['mail']));

        Twilio::assertMessageNotSent($to, $msg);
    }

    public function test_fake_message_is_sent_through_notifications_and_twilio_channel()
    {
        $msg = 'fake message from mars';
        $to  = '+12349010035';

        Twilio::fake();
        NotificationFacade::route('twilio', $to)->notify(new FakeTwilioNotification($msg, ['twilio']));
        Twilio::assertMessageSent($to, $msg);
    }

    public function test_fake_works_with_notifications()
    {
        $msg = 'fake message from mars';
        $to  = '+12349010035';

        $this->superadmin()->phoneNumbers()->delete();

        $this->assertFalse($this->superadmin()->phoneNumbers()->exists());

        $this->superadmin()->phoneNumbers()->create([
            'number' => $to,
            'type'   => PhoneNumber::MOBILE,
        ]);

        Twilio::fake();
        $this->superadmin()->notify(new SendSms($msg));
        Twilio::assertMessageSent($to, $msg);
    }

    public function test_it_can_fake_messages()
    {
        $msg = 'fake message';
        $to  = '+12349010035';

        $this->twilio()->sendMessage(
            (new TwilioSmsMessage())
                ->content($msg),
            $to
        );

        $this->twilio()->assertMessageSent($to, $msg);
    }
}

class FakeTwilioNotification extends Notification
{
    use Queueable;

    public $id = 'fake-id';

    /**
     * @var string
     */
    public $message;
    public $via;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, array $via = ['twilio'])
    {
        $this->message = $message;
        $this->via     = $via;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())->from('hello@example.com')->subject('Hello');
    }

    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->message);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->via;
    }
}
