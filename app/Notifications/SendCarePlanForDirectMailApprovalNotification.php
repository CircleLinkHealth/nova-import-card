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
use URL;

class SendCarePlanForDirectMailApprovalNotification extends Notification implements DirectMailableNotification
{
    use Queueable;
    /**
     * @var User
     */
    protected $patientUser;
    
    /**
     * Create a new notification instance.
     *
     * @param User $patientUser
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
    }

    public function directMailBody($notifiable): string
    {
        $identifier = $this->patientUser->carePlan->id;

        return "Dear {$notifiable->getFullName()},
            \n
            Please review attached Care Plan for {$this->patientUser->getFullName()}.
            \n
            To approve, please respond to this message with \"#approve$identifier\".
            \n
            To make changes, respond to this message with \"#change$identifier\" on the first line of your message, and your changes in plain text below. Or, copy/paste \"{$this->passwordlessLoginLink($notifiable)}\" into a web browser and adjust the care plan there, then click the \"Approve and View Next\" button in top-right.
            \n
            Thank you,
            \n
            CircleLink Team
";
    }

    public function directMailSubject($notifiable): string
    {
        return "{$this->patientUser->getFullName()}'s CCM Care Plan to approve!";
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
     * @return SimpleNotification
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
    
    /**
     * @param $notifiable
     *
     * @return string
     */
    public function passwordlessLoginLink($notifiable)
    {
        $token = PasswordlessLoginToken::create(
            [
                'user_id' => $notifiable->id,
                'token'   => sha1(str_random(15).time()),
            ]
        );

        return URL::temporarySignedRoute('login.token.validate', now()->addWeeks(2), [$token->token]);
    }
}
