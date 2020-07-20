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

    /** @var string */
    private $drLastName;

    /** @var bool */
    private $isReminder;

    /** @var string */
    private $nurseFirstName;

    /** @var string */
    private $practice;

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

    private function setup(User $patient)
    {
        // these shouldn't be empty at this point,
        // i'm making the checks so it wouldn't fail with test data
        $this->nurseFirstName = '???';
        if ($this->unsuccessfulCall->outboundUser) {
            $this->nurseFirstName = $this->unsuccessfulCall->outboundUser->first_name;
        }

        $this->drLastName = '???';
        $dr               = $patient->billingProviderUser();
        if ($dr) {
            $this->drLastName = $dr->last_name;
        }

        $this->practice = '???';
        if ($patient->primaryPractice) {
            $this->practice = $patient->primaryPractice->display_name;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $this->setup($notifiable);

        return [
            'nurseFirstName' => $this->nurseFirstName,
            'drLastName'     => $this->drLastName,
            'practice'       => $this->practice,
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

    private function getMailMessage(User $patient): MailMessage
    {
        if ( ! $this->isReminder) {
            $subject = "Nurse $this->nurseFirstName just called. When's a better time?";
        } else {
            $subject = "Nurse $this->nurseFirstName called 2 days ago. When's a better time?";
        }

        $line1 = "Hi, it's Dr. $this->drLastName's care program at $this->practice!";
        if ( ! $this->isReminder) {
            $line2 = "Nurse $this->nurseFirstName just tried calling.";
        } else {
            $line2 = "Nurse $this->nurseFirstName tried calling 2 days ago and we haven't heard from you.";
        }

        $line3 = "Please reply with date(s) and time(s) you are available for Nurse $this->nurseFirstName to call you back.";

        $line4 = "We'll forward your message to Nurse $this->nurseFirstName.";

        $mailMessage           = new MailMessage();
        $mailMessage->viewData = ['excludeLogo' => true, 'practiceName' => $this->practice];

        return $mailMessage
            ->subject($subject)
            ->greeting($line1)
            ->line($line2)
            ->line($line3)
            ->line($line4)
            ->line(new HtmlString("Thanks!<br/>Dr. $this->drLastName's Office"));
    }

    private function getMessage(User $patient, string $via)
    {
        $this->setup($patient);
        if ('mail' === $via) {
            return $this->getMailMessage($patient);
        }

        return $this->getSmsMessage($patient);
    }

    private function getSmsMessage(User $patient): TwilioSmsMessage
    {
        $part1 = "Hi it's $this->drLastName's care program at $this->practice! ";

        if ( ! $this->isReminder) {
            $part2 = "Nurse $this->nurseFirstName just tried calling. ";
        } else {
            $part2 = "Nurse $this->nurseFirstName tried calling 2 days ago. ";
        }

        $part3 = "What day and time would be best to call you back?\n\n(we'll forward your response to nurse $this->nurseFirstName)";

        return (new TwilioSmsMessage())
            ->content($part1.$part2.$part3);
    }
}
