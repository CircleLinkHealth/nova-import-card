<?php

namespace App\Notifications;

use App\NotifiableUser;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SurveyInvitationLink extends Notification
{
    use Queueable;

    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”] at {time}[hh:mm am/pm].";
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_ONLY = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”].";
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE = "Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";

    const SMS_TEXT_FOR_VITALS = "Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";


    const EMAIL_GREETING = "Hello!";
    const EMAIL_ACTION = "Open Survey";
    const EMAIL_LINE_1 = "Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can.";
    const EMAIL_LINE_2 = "Please call {clhNumber} if you have any questions.";

    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $surveyName;
    private $via;

    /**
     * Create a new notification instance.
     *
     * @param string $url
     * @param string $surveyName
     * @param $via 'sms' or 'mail'
     */
    public function __construct(string $url, string $surveyName, $via)
    {
        $this->url        = $url;
        $this->surveyName = $surveyName;
        $this->via        = $via;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->via === 'mail'
            ? ['mail']
            : [TwilioChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param NotifiableUser $notifiableUser
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(NotifiableUser $notifiableUser)
    {
        $providerLastName = $notifiableUser->user->billingProviderUser()->last_name;
        $practiceName     = $notifiableUser->user->primaryPractice->display_name;

        //todo: check if we have known appointment and select appropriate SMS message
        //todo: use $surveyName to decide the body of the message

        $line1 = Str::replaceFirst("{primaryPhysicianLastName}", $providerLastName,
            self::EMAIL_LINE_1);
        $line1 = Str::replaceFirst("{practiceName}", $practiceName, $line1);
        $line2 = Str::replaceFirst("{clhNumber}", config('services.twilio.from'), self::EMAIL_LINE_2);

        return (new MailMessage)
            ->greeting(self::EMAIL_GREETING)
            ->line($line1)
            ->action(self::EMAIL_ACTION, $this->url)
            ->line($line2);
    }

    /**
     * @param NotifiableUser $notifiableUser
     *
     * @return TwilioSmsMessage
     */
    public function toTwilio(NotifiableUser $notifiableUser)
    {
        $providerLastName = $notifiableUser->user->billingProviderUser()->last_name;
        $practiceName     = $notifiableUser->user->primaryPractice->display_name;

        //todo: check if we have known appointment and select appropriate SMS message
        //todo: use $surveyName to decide the body of the message

        $text = Str::replaceFirst("{primaryPhysicianLastName}", $providerLastName,
            self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE);
        $text = Str::replaceFirst("{practiceName}", $practiceName, $text);
        $text = Str::replaceFirst("{clhNumber}", config('services.twilio.from'), $text);
        $text = $text . "\n" . $this->url;

        return (new TwilioSmsMessage())
            ->content($text);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
