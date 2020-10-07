<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public $queue = 'high';

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

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
        $fromName = $notifiable->isParticipant() ? $notifiable->getPrimaryPracticeName() : config('mail.from.name');

        return (new MailMessage())
            ->from(config('mail.from.address'), $fromName)
            ->view('vendor.notifications.email', [
                'greeting'     => 'You are receiving this email because we received a password reset request for your account.',
                'actionText'   => 'Reset Password',
                'actionUrl'    => $this->resetUrl($notifiable),
                'introLines'   => ['Click on the button below to reset your password. As a security measure, your reset token expires in one hour.'],
                'outroLines'   => ['If you did not request a password reset, no further action is required.'],
                'level'        => '',
                'practiceName' => $notifiable->isParticipant() ? $notifiable->getPrimaryPracticeName() : null,
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

    public function viaQueues()
    {
        return [
            'mail' => 'high',
        ];
    }

    /**
     * Send email so we prefill email-input
     * If notifiable is patient, sent practice ID so we can replace CLH logo with Practice Name.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    private function resetUrl($notifiable)
    {
        $args = [
            'token' => $this->token,
            'email' => $notifiable->email,
        ];

        if ($notifiable->isParticipant()) {
            $args['practice_id'] = $notifiable->getPrimaryPracticeId();
        }

        return route('password.reset', $args);
    }
}
