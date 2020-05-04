<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\NotifiableUser;
use Carbon\Carbon;
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

    const DATE_FORMAT                                 = 'm/d/y';
    const EMAIL_ACTION                                = 'Open Survey';
    const EMAIL_ACTION_VITALS                         = 'Input Vitals';
    const EMAIL_GREETING                              = 'Hello!';
    const EMAIL_LINE_1                                = 'Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this wellness survey as soon as you can.';
    const EMAIL_LINE_1_KNOWN_APPOINTMENT              = 'Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this wellness survey before your scheduled appointment on {date} at {time}.';
    const EMAIL_LINE_1_KNOWN_APPOINTMENT_NO_PHYSICIAN = 'Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this wellness survey before your scheduled appointment on {date} at {time}.';
    const EMAIL_LINE_1_NO_PHYSICIAN                   = '{practiceName} practice requests you complete this wellness survey as soon as you can.';
    const EMAIL_LINE_1_VITALS                         = '{practiceName} has requested you to input vitals for a Wellness Visit.';
    const EMAIL_LINE_2_VITALS                         = 'Please talk to your intake team if you have any questions.';

    const EMAIL_SUBJECT              = 'Annual Wellness Survey - {primaryPhysicianLastName} at {practiceName}';
    const EMAIL_SUBJECT_NO_PHYSICIAN = 'Annual Wellness Survey - {practiceName}';
    const EMAIL_SUBJECT_VITALS       = 'Annual Wellness Survey - {practiceName}';

    const PRACTICE_NAME_ALTERNATIVE                             = 'CircleLink Health';
    const SALUTATION                                            = 'Regards';
    const SALUTATION_TEAM                                       = '{practiceName}';
    const SALUTATION_TEAM_VITALS                                = 'CircleLink Team';
    const SALUTATION_VITALS                                     = 'Thanks,';
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME              = 'Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this wellness survey before your scheduled appointment on {date} at {time}: {url} .';
    const SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME_NO_PHYSICIAN = 'Hello! {practiceName} practice requests you complete this wellness survey before your scheduled appointment on {date} at {time}: {url} .';
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE                 = 'Hello! Dr. {primaryPhysicianLastName} at {practiceName} requests you complete this health survey as soon as you can: {url} .';
    const SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE_NO_PHYSICIAN    = 'Hello! {practiceName} practice requests you complete this health survey as soon as you can: {url} .';
    const SUPPORT_TEXT                                          = 'Please call {clhNumber} if you have any questions.';
    const TIME_FORMAT                                           = 'h:i a';

    const VITALS = 'Vitals';

    /**
     * @var Carbon
     */
    private $appointment;

    /**
     * @var string
     */
    private $practiceName;

    /**
     * @var string
     */
    private $providerFullName;

    /**
     * @var string
     */
    private $surveyName;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $via;

    /**
     * Create a new notification instance.
     *
     * @param $via 'sms' or 'mail' or null
     * @param string|null $practiceName
     * @param string|null $providerFullName
     */
    public function __construct(
        string $url,
        string $surveyName,
        string $via = null,
        $practiceName = null,
        $providerFullName = null,
        Carbon $appointment = null
    ) {
        $this->url              = $url;
        $this->surveyName       = $surveyName;
        $this->via              = $via;
        $this->practiceName     = $practiceName;
        $this->providerFullName = $providerFullName;
        $this->appointment      = $appointment;
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
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(NotifiableUser $notifiableUser)
    {
        $isVitalsSurvey = self::VITALS === $this->surveyName;

        if ($isVitalsSurvey) {
            $from    = self::PRACTICE_NAME_ALTERNATIVE;
            $subject = Str::replaceFirst(
                '{practiceName}',
                $this->practiceName,
                self::EMAIL_SUBJECT_VITALS
            );
            $line1 = Str::replaceFirst(
                '{practiceName}',
                $this->practiceName,
                self::EMAIL_LINE_1_VITALS
            );
            $line2          = self::EMAIL_LINE_2_VITALS;
            $action         = self::EMAIL_ACTION_VITALS;
            $salutation     = self::SALUTATION_VITALS;
            $salutationTeam = self::SALUTATION_TEAM_VITALS;
        } else {
            $from    = $this->practiceName ?? self::PRACTICE_NAME_ALTERNATIVE;
            $subject = $this->providerFullName
                ? self::EMAIL_SUBJECT
                : self::EMAIL_SUBJECT_NO_PHYSICIAN;
            $subject = Str::replaceFirst('{primaryPhysicianLastName}', $this->providerFullName, $subject);
            $subject = Str::replaceFirst('{practiceName}', $this->practiceName, $subject);

            if ($this->appointment) {
                $line1 = $this->providerFullName
                    ? self::EMAIL_LINE_1_KNOWN_APPOINTMENT
                    : self::EMAIL_LINE_1_KNOWN_APPOINTMENT_NO_PHYSICIAN;
                $line1 = Str::replaceFirst('{date}', $this->appointment->format(self::DATE_FORMAT), $line1);
                $line1 = Str::replaceFirst('{time}', $this->appointment->format(self::TIME_FORMAT), $line1);
            } else {
                $line1 = $this->providerFullName
                    ? self::EMAIL_LINE_1
                    : self::EMAIL_LINE_1_NO_PHYSICIAN;
            }
            $line1          = Str::replaceFirst('{primaryPhysicianLastName}', $this->providerFullName, $line1);
            $line1          = Str::replaceFirst('{practiceName}', $this->practiceName, $line1);
            $line2          = Str::replaceFirst('{clhNumber}', config('services.twilio.from'), self::SUPPORT_TEXT);
            $action         = self::EMAIL_ACTION;
            $salutation     = self::SALUTATION;
            $salutationTeam = Str::replaceFirst(
                '{practiceName}',
                $this->practiceName ?? self::PRACTICE_NAME_ALTERNATIVE,
                self::SALUTATION_TEAM
            );
        }

        return (new MailMessage())
            ->from('support@circlelinkhealth.com', $from)
            ->subject($subject)
            ->greeting(self::EMAIL_GREETING)
            ->salutation($salutationTeam)
            ->line($line1)
            ->action($action, $this->url)
            ->line($line2)
            ->line($salutation);
    }

    /**
     * @return TwilioSmsMessage
     */
    public function toTwilio(NotifiableUser $notifiableUser)
    {
        if ($this->appointment) {
            $text = $this->providerFullName
                ? self::SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME
                : self::SMS_TEXT_FOR_KNOWN_APPOINTMENT_DATE_TIME_NO_PHYSICIAN;

            $text = Str::replaceFirst('{date}', $this->appointment->format(self::DATE_FORMAT), $text);
            $text = Str::replaceFirst('{time}', $this->appointment->format(self::TIME_FORMAT), $text);
        } else {
            $text = $this->providerFullName
                ? self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE
                : self::SMS_TEXT_FOR_UNKNOWN_APPOINTMENT_DATE_NO_PHYSICIAN;
        }

        $text = Str::replaceFirst('{primaryPhysicianLastName}', $this->providerFullName, $text);
        $text = Str::replaceFirst('{practiceName}', $this->practiceName, $text);

        $text        = $text        = Str::replaceFirst('{url}', $this->url, $text);
        $supportText = Str::replaceFirst('{clhNumber}', config('services.twilio.from'), self::SUPPORT_TEXT);
        $text        = $text."\n".$supportText;

        return (new TwilioSmsMessage())
            ->content($text);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via(NotifiableUser $notifiable)
    {
        if ( ! $this->via) {
            $channels = [];
            $target   = $notifiable->user;
            $phone    = $target->getPhone();
            if ( ! empty($phone)) {
                $channels[] = TwilioChannel::class;
            }
            if ( ! empty($target->email)) {
                $channels[] = 'mail';
            }

            return $channels;
        }

        return 'mail' === $this->via
            ? ['mail']
            : [TwilioChannel::class];
    }
}
