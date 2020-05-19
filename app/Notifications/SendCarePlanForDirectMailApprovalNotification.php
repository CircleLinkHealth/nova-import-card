<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\DirectMailableNotification;
use App\Notifications\Channels\DirectMailChannel;
use App\PasswordlessLoginToken;
use App\ValueObjects\SimpleNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use URL;

class SendCarePlanForDirectMailApprovalNotification extends Notification implements DirectMailableNotification
{
    use Queueable;
    /**
     * @var User
     */
    protected $patientUser;
    /**
     * @var \Illuminate\Database\Eloquent\Model|PasswordlessLoginToken
     */
    private $passwordlessLoginToken;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
    }

    public function directMailBody($notifiable): string
    {
        $identifier = $this->patientUser->carePlan->id;

        return "Dear {$notifiable->getFullName()},"
            .PHP_EOL.PHP_EOL.
            "Please review attached Care Plan for {$this->patientUser->getFullName()}"
            .PHP_EOL.PHP_EOL.
            'To approve, please respond to this message with "#approve'.$identifier.'"'
            .PHP_EOL.PHP_EOL.
            "To make changes, respond to this message with \"#change$identifier\" on the first line of your message, and your changes in plain text below. Or, copy/paste single-use URL/Link \"{$this->passwordlessLoginLink($notifiable)}\" into a web browser and adjust the care plan there, then click the \"Approve and View Next\" button in top-right."
            .PHP_EOL.PHP_EOL.PHP_EOL.
            'Thank you,'
            .PHP_EOL.
            'CircleLink Team'
;
    }

    public function directMailSubject($notifiable): string
    {
        return "{$this->patientUser->getFullName()}'s CCM Care Plan to approve!";
    }

    /**
     * @param $notifiable
     *
     * @return string
     */
    public function passwordlessLoginLink($notifiable)
    {
        return URL::route('passwordless.login.for.careplan.approval', [$this->token($notifiable)->token, $this->patientUser->id]);
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
            'message' => [
                'subject' => $this->directMailSubject($notifiable),
                'body'    => $this->directMailBody($notifiable),
            ],
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @throws \Exception
     */
    public function toDirectMail($notifiable): SimpleNotification
    {
        return (new SimpleNotification())
            ->setSubject($this->directMailSubject($notifiable))
            ->setBody($this->directMailBody($notifiable))
            ->setPatient($this->patientUser)
            ->setFilePath($this->patientUser->carePlan->toPdf())
            ->setFileName("{$this->patientUser->getFullName()}'s CCM Care Plan.pdf");
    }

    public function token($notifiable)
    {
        if ( ! $this->passwordlessLoginToken) {
            do {
                $saved = false;
                $token = Str::random(6);

                if ( ! PasswordlessLoginToken::where('token', $token)->exists()) {
                    $this->passwordlessLoginToken = PasswordlessLoginToken::create(
                        [
                            'user_id' => $notifiable->id,
                            'token'   => $token,
                        ]
                    );

                    $saved = true;
                }
            } while ( ! $saved);
        }

        return $this->passwordlessLoginToken;
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
        return ['database', DirectMailChannel::class];
    }
}
