<?php

namespace App\Notifications;

use App\NotifiableUser;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SurveyInvitationLink extends Notification implements ShouldQueue
{
    use Queueable;

    const VITALS = 'Vitals';
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”] at {time}[hh:mm am/pm].";
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_ONLY = "Hello! Dr. {primaryPhysicianLastName} requests you complete this wellness survey before your scheduled appointment on {date}[“mm/dd/yy”].";
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE = "Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE_NO_PHYSICIAN = "Hello! {practiceName} practice requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";

    const SMS_TEXT_FOR_VITALS = "Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can. Please call {clhNumber} if you have any questions.";

    const EMAIL_SUBJECT = "Annual Wellness Survey - {primaryPhysicianLastName} at {practiceName}";
    const EMAIL_SUBJECT_VITALS = "Annual Wellness Survey - {practiceName}";
    const EMAIL_SUBJECT_NO_PHYSICIAN = "Annual Wellness Survey - {practiceName}";
    const EMAIL_GREETING = "Hello!";
    const EMAIL_ACTION = "Open Survey";
    const EMAIL_ACTION_VITALS = "Input Vitals";
    const EMAIL_LINE_1 = "Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can.";
    const EMAIL_LINE_1_VITALS = "{practiceName} has requested you to input vitals for a Wellness Visit.";
    const EMAIL_LINE_1_NO_PHYSICIAN = "{practiceName} practice requests you complete this health survey as soon as you can.";
    const EMAIL_LINE_2 = "Please call {clhNumber} if you have any questions.";
    const EMAIL_LINE_2_VITALS = "Please talk to your intake team if you have any questions.";
    const SALUTATION = "Regards";
    const SALUTATION_TEAM = "{practiceName}";
    const SALUTATION_VITALS = "Thanks,";
    const SALUTATION_TEAM_VITALS = "CircleLink Team";

    const PRACTICE_NAME_ALTERNATIVE = "CircleLink Health";

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
     * @var string
     */
    private $practiceName;

    /**
     * @var string
     */
    private $providerFullName;

    /**
     * Create a new notification instance.
     *
     * @param string $url
     * @param string $surveyName
     * @param $via 'sms' or 'mail' or null
     * @param null $practiceName
     * @param null $providerFullName
     */
    public function __construct(
        string $url,
        string $surveyName,
        $via = null,
        $practiceName = null,
        $providerFullName = null
    ) {
        $this->url              = $url;
        $this->surveyName       = $surveyName;
        $this->via              = $via;
        $this->practiceName     = $practiceName;
        $this->providerFullName = $providerFullName;
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
        if ( ! $this->via) {
            $channels = [];
            /** @var User $target */
            $target = $notifiable;
            $phone  = $target->getPhone();
            if ( ! empty($phone)) {
                $channels[] = TwilioChannel::class;
            }
            if ( ! empty($target->email)) {
                $channels[] = 'mail';
            }

            return $channels;
        }

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
        //todo: check if we have known appointment and select appropriate SMS message

        $isVitalsSurvey = $this->surveyName === self::VITALS;

        if ($isVitalsSurvey) {
            $from           = self::PRACTICE_NAME_ALTERNATIVE;
            $subject        = Str::replaceFirst("{practiceName}", $this->practiceName,
                self::EMAIL_SUBJECT_VITALS);
            $line1          = Str::replaceFirst("{practiceName}", $this->practiceName,
                self::EMAIL_LINE_1_VITALS);
            $line2          = self::EMAIL_LINE_2_VITALS;
            $action         = self::EMAIL_ACTION_VITALS;
            $salutation     = self::SALUTATION_VITALS;
            $salutationTeam = self::SALUTATION_TEAM_VITALS;
        } else {

            $from = $this->practiceName ?? self::PRACTICE_NAME_ALTERNATIVE;

            if ($this->providerFullName) {
                $subject = Str::replaceFirst("{primaryPhysicianLastName}", $this->providerFullName,
                    self::EMAIL_SUBJECT);
                $subject = Str::replaceFirst("{practiceName}", $this->practiceName, $subject);
                $line1   = Str::replaceFirst("{primaryPhysicianLastName}", $this->providerFullName,
                    self::EMAIL_LINE_1);
                $line1   = Str::replaceFirst("{practiceName}", $this->practiceName, $line1);
            } else {
                $subject = Str::replaceFirst("{practiceName}", $this->practiceName,
                    self::EMAIL_SUBJECT_NO_PHYSICIAN);
                $line1   = Str::replaceFirst("{practiceName}", $this->practiceName, self::EMAIL_LINE_1_NO_PHYSICIAN);
            }

            $line2          = Str::replaceFirst("{clhNumber}", config('services.twilio.from'), self::EMAIL_LINE_2);
            $action         = self::EMAIL_ACTION;
            $salutation     = self::SALUTATION;
            $salutationTeam = Str::replaceFirst("{practiceName}",
                $this->practiceName ?? self::PRACTICE_NAME_ALTERNATIVE, self::SALUTATION_TEAM);
        }

        return (new MailMessage)
            ->from("support@circlelinkhealth.com", $from)
            ->subject($subject)
            ->greeting(self::EMAIL_GREETING)
            ->salutation($salutationTeam)
            ->line($line1)
            ->action($action, $this->url)
            ->line($line2)
            ->line($salutation);
    }

    /**
     * @param NotifiableUser $notifiableUser
     *
     * @return TwilioSmsMessage
     */
    public function toTwilio(NotifiableUser $notifiableUser)
    {
        //todo: check if we have known appointment and select appropriate SMS message
        //todo: use $surveyName to decide the body of the message

        if ($this->providerFullName) {
            $text = Str::replaceFirst("{primaryPhysicianLastName}", $this->providerFullName,
                self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE);
            $text = Str::replaceFirst("{practiceName}", $this->practiceName, $text);
        } else {
            $text = Str::replaceFirst("{practiceName}", $this->practiceName,
                self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE_NO_PHYSICIAN);
        }

        $text = Str::replaceFirst("{clhNumber}", config('services.twilio.from'), $text);
        $text = $text . "\n" . $this->url;

        return (new TwilioSmsMessage())
            ->content($text);
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
            //
        ];
    }
}
