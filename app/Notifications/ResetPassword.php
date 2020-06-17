<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Only Users can reset passwords.
     *
     * @var User
     */
    private $notifiable;

    /**
     * Create a new notification instance.
     *
     * @param mixed $token
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $this->notifiable = $notifiable;

        return (new MailMessage())
            ->mailer('postmark')
            //If notifiable is patient, we need to replace any references to CircleLink Health with Practice Name
            ->from('noreply@circlelinkhealth.com', $notifiable->isParticipant() ? $notifiable->getPrimaryPracticeName() : 'CircleLink Health')
            ->view('vendor.notifications.email', [
                'greeting'     => 'You are receiving this email because we received a password reset request for your account.',
                'actionText'   => 'Reset Password',
                'actionUrl'    => $this->resetUrl(),
                'introLines'   => ['Click on the button below to reset your password. As a security measure, your reset token expires in one hour.'],
                'outroLines'   => ['If you did not request a password reset, no further action is required.'],
                'level'        => '',
                'practiceName' => $notifiable->isParticipant() ? $notifiable->getPrimaryPracticeName() : null,
                //todo: fix - this is not used in the email currently
                'saasAccountName' => $notifiable->saasAccountName(),
            ]);
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
        return ['mail'];
    }

    /**
     * Send email so we prefill email-input
     * If notifiable is patient, sent practice ID so we can replace CLH logo with Practice Name.
     *
     * @return string
     */
    private function resetUrl()
    {
        $args = [
            'token' => $this->token,
            'email' => $this->notifiable->email,
        ];

        if ($this->notifiable->isParticipant()) {
            $args['practice_id'] = $this->notifiable->getPrimaryPracticeId();
        }

        return route('password.reset', $args);
    }
}
