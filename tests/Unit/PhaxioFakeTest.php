<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Contracts\FaxableNotification;
use App\Notifications\SendSms;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Concerns\PhaxioFake\Phaxio;
use Tests\Concerns\PhaxioFake\WithPhaxioMock;
use Tests\Concerns\TwilioFake\Twilio;
use Tests\CustomerTestCase;

class PhaxioFakeTest extends CustomerTestCase
{
    use WithPhaxioMock;

    public function test_assert_nothing_sent()
    {
        $file       = 'path/to/file';
        $to         = '+12349010035';
        $numberSent = 0;

        for ($i = 0; $i < $numberSent; ++$i) {
            $this->phaxio()->send([
                'to'   => $to,
                'file' => $file,
            ]);
        }

        try {
            $wrongNumberSent = $numberSent + 1;
            $this->phaxio()->assertNumberOfFaxesSent($wrongNumberSent);
        } catch (ExpectationFailedException $e) {
            $expected = "Failed to send [$wrongNumberSent] faxes. [$numberSent] were sent.\nFailed asserting that false is true.";

            $this->assertTrue($expected === $e->getMessage(), "Failed asserting that expected `$expected` equals `{$e->getMessage()}`");
        }

        $this->phaxio()->assertNothingSent();
    }

    public function test_assert_number_sent_counts_correctly()
    {
        $file       = 'path/to/file';
        $to         = '+12349010035';
        $numberSent = 5;

        for ($i = 0; $i < $numberSent; ++$i) {
            $this->phaxio()->send([
                'to'   => $to,
                'file' => $file,
            ]);
        }

        try {
            $wrongNumberSent = $numberSent - 1;
            $this->phaxio()->assertNumberOfFaxesSent($wrongNumberSent);
        } catch (ExpectationFailedException $e) {
            $expected = "Failed to send [$wrongNumberSent] faxes. [$numberSent] were sent.\nFailed asserting that false is true.";

            $this->assertTrue($expected === $e->getMessage(), "Failed asserting that expected `$expected` equals `{$e->getMessage()}`");
        }

        $this->phaxio()->assertNumberOfFaxesSent($numberSent);
    }

    public function test_fake_message_is_not_sent_if_notification_does_not_have_twilio_channel()
    {
        $file = 'fake message from mars';
        $to   = '+12349010035';

        NotificationFacade::fake();
        Phaxio::fake();

        /** @var AnonymousNotifiable $anonymous */
        $anonymous = NotificationFacade::route('phaxio', $to);
        $anonymous->route('mail', 'test@test.com');
        $anonymous->notify(new FakeNotification($file, ['mail']));

        Phaxio::assertFaxNotSent($to, $file);
    }

    public function test_fake_message_is_sent_through_notifications_and_twilio_channel()
    {
        $msg = 'fake message from mars';
        $to  = '+12349010035';

        Phaxio::fake();
        NotificationFacade::route('phaxio', $to)->notify(new FakeNotification($msg, ['phaxio']));
        Phaxio::assertFaxSent($to, $msg);
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

        $this->phaxio()->send([
            'to'   => $to,
            'file' => $msg,
        ]);

        $this->phaxio()->assertFaxSent($to, $msg);
    }
}

class FakeNotification extends Notification implements FaxableNotification
{
    use Queueable;

    /**
     * @var string
     */
    public $filePath;

    public $id = 'fake-id';
    public $via;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $filePath, array $via = ['phaxio'])
    {
        $this->filePath = $filePath;
        $this->via      = $via;
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

    public function toFax($notifiable = null): array
    {
        return [
            'file' => $this->filePath,
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
