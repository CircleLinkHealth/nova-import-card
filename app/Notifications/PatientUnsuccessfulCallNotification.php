<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Call;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Propaganistas\LaravelPhone\Exceptions\NumberFormatException;

class PatientUnsuccessfulCallNotification extends Notification
{
    use Queueable;

    /** @var bool */
    private $isReminder;

    /** @var Call */
    private $unsuccessfulCall;

    /**
     * Create a new notification instance.
     */
    public function __construct(Call $call, bool $isReminder = false)
    {
        $this->unsuccessfulCall = $call;
        $this->isReminder       = $isReminder;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
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
     */
    public function toMail($notifiable): MailMessage
    {
        return $this->getMessage($notifiable, 'mail');
    }

    public function toTwilio(User $notifiable): TwilioMessage
    {
        return $this->getMessage($notifiable, 'twilio');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['database'];
        if ( ! empty($notifiable->email) && ! Str::contains($notifiable->email, ['@careplanmanager.com', '@example.com', '@noEmail.com'])) {
            $channels[] = 'mail';
        }

        try {
            if ( ! empty($notifiable->getPhoneNumberForSms())) {
                $channels[] = 'twilio';
            }
        } catch (NumberFormatException $e) {
            \Log::warning($e->getMessage());
        }

        return $channels;
    }

    private function getMailMessage(User $patient, string $nurseFirstName, string $drLastName, string $practice): MailMessage
    {
        if ( ! $this->isReminder) {
            $subject = "Nurse $nurseFirstName just called. When's a better time?";
        } else {
            $subject = "Nurse $nurseFirstName called 2 days ago. When's a better time?";
        }

        $line1 = "Hi, it's $drLastName's care program at $practice!";
        if ( ! $this->isReminder) {
            $line2 = "Nurse $nurseFirstName just tried calling.";
        } else {
            $line2 = "Nurse $nurseFirstName tried calling 2 days ago and we haven't heard from you.";
        }

        $line3 = "Please reply with date(s) and time(s) you are available for Nurse $nurseFirstName to call you back.";

        $line4 = "We'll forward your message to Nurse $nurseFirstName.";

        $mailMessage           = new MailMessage();
        $mailMessage->viewData = ['excludeLogo' => true, 'practiceName' => $practice];

        return $mailMessage
            ->subject($subject)
            ->greeting($line1)
            ->line($line2)
            ->line($line3)
            ->line($line4)
            ->line(new HtmlString("Thanks!<br/>Dr. $drLastName's Office"));
    }

    private function getMessage(User $patient, string $via)
    {
        // these shouldn't be empty at this point,
        // i'm making the checks so it wouldn't fail with test data
        $nurseFirstName = '???';
        if ($this->unsuccessfulCall->outboundUser) {
            $nurseFirstName = $this->unsuccessfulCall->outboundUser->first_name;
        }

        $drLastName = '???';
        $dr         = $patient->billingProviderUser();
        if ($dr) {
            $drLastName = $dr->last_name;
        }

        $practice = '???';
        if ($patient->primaryPractice) {
            $practice = $patient->primaryPractice->display_name;
        }

        if ('mail' === $via) {
            return $this->getMailMessage($patient, $nurseFirstName, $drLastName, $practice);
        }

        return $this->getSmsMessage($patient, $nurseFirstName, $drLastName, $practice);
    }

    private function getSmsMessage(User $patient, string $nurseFirstName, string $drLastName, string $practice): TwilioSmsMessage
    {
        $part1 = "Hi it's $drLastName's care program at $practice! ";

        if ( ! $this->isReminder) {
            $part2 = "Nurse $nurseFirstName just tried calling. ";
        } else {
            $part2 = "Nurse $nurseFirstName tried calling 2 days ago. ";
        }

        $part3 = "What day and time would be best to call you back?\n\n(we'll forward your response to nurse $nurseFirstName)";

        return (new TwilioSmsMessage())
            ->content($part1.$part2.$part3);
    }
}
