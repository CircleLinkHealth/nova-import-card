<?php

namespace CircleLinkHealth\SelfEnrollment\Notifications;

use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioSmsMessage;

class NotifySelfEnrollmentUserErrorIsFixed extends Notification
{
    use Queueable;

    private string $invitationLink;
    private string $line1;
    private $line2Sms;
    private string $line2Email;

    /**
     * Create a new notification instance.
     *
     * @param string $invitationLink
     */
    public function __construct(string $invitationLink)
    {
        $this->invitationLink = $invitationLink;
        $this->line1 = $this->subjectLine1();
        $this->line2Sms = $this->subjectLine2Sms();
        $this->line2Email = $this->subjectLine2ForEmail();
    }

    /**
     * @param User $notifiable
     */
    public function toTwilio(User $notifiable)
    {
        if (empty($this->invitationLink)) {
            throw new InvalidArgumentException("`invitationLink` cannot be empty. User ID {$notifiable->id}");
        }

        $subject = $this->getSubjectSms();

        return (new TwilioSmsMessage())
            ->content($subject);
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = ['mail', 'twilio'];
        if (Str::contains($notifiable->email, ['@careplanmanager.com', '@example.com', '@noEmail.com'])){
            unset($channels[array_search('mail', $channels)]);
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $fromName = null;

        if ( ! empty($notifiable->primaryPractice) && ! empty($notifiable->primaryPractice->display_name)) {
            $fromName = $notifiable->primaryPractice->display_name;
        }

        if (empty($fromName)) {
            $fromName = config('mail.marketing_from.name');
        }

        if (empty($this->invitationLink)) {
            throw new InvalidArgumentException("`invitationLink` cannot be empty. User ID {$notifiable->id}");
        }

        $mailMessage           = new MailMessage();
        $mailMessage->viewData = ['excludeLogo' => true, 'practiceName' => $fromName];

        return $mailMessage
            ->mailer('smtp')
            ->from(config('mail.marketing_from.address'), $fromName)
            ->subject('Wellness Program')
            ->line($this->line1)
            ->line($this->line2Email)
            ->action('Get my Care Coach', $this->invitationLink);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    private function getSubjectSms()
    {
        return "$this->line1 $this->line2Sms $this->invitationLink";
    }

    private function subjectLine1()
    {
        return "Our apologies about the error you experienced earlier when trying to get your care coach.";
    }

    private function subjectLine2Sms()
    {
        return "Our team has fixed this error. Whenever you have a chance, please visit";
    }

    private function subjectLine2ForEmail()
    {
        return "Our team has fixed this error. Whenever you have a chance, please use the button below to get your care coach.";
    }
}
