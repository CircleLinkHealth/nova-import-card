<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

/**
 * This notification is sent to the patient both when Careplan is QA approved by CLH, and when it's Provider
 * approved. The first case we send the patient a button link to the password reset page, while on the second we
 * send them a link to CPM, with a hyperlink of the reset page below.
 */
class NotifyPatientCarePlanApproved extends Notification
{
    use Queueable;

    public $url;

    /**
     * @var CarePlan
     */
    private $carePlan;

    /**
     * @var array
     */
    private $channels = ['database'];

    /**
     * @var
     */
    private $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(CarePlan $carePlan, array $channels = ['mail'])
    {
        $this->channels = array_merge($channels, $this->channels);
        $this->carePlan = $carePlan;
    }

    /**
     * If Careplan QA approved, prompt patient to setup password
     * If Careplan Provider approved, prompt them to login, while still displaying url to setup password in the email.
     *
     * @return string
     */
    public function getActionText()
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status
            ? 'Setup Password'
            : 'Login to View Care Plan';
    }

    /**
     * @param mixed $notifiable
     *
     * @return string
     */
    public function getActionUrl($notifiable)
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status
            ? $this->resetUrl($notifiable)
            : route('home', [
                'practice_id' => $notifiable->program_id,
            ]);
    }

    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getBody()
    {
        if (CarePlan::PROVIDER_APPROVED === $this->carePlan->status) {
            return 'Your Care Plan is now approved and can be viewed by logging in!';
        }

        return 'Thanks for joining our Wellness Program! Your Care Plan has been sent to your doctor for approval. To view, please click below button to setup a password.';
    }

    /**
     * Get the mail's subject.
     *
     * @return string
     */
    public function getSubject()
    {
        if (CarePlan::PROVIDER_APPROVED === $this->carePlan->status) {
            return 'Your Care Plan has just been approved';
        }

        return 'Your Care Plan has been sent to your doctor for approval';
    }

    /**
     * Send practice id so we can replace CLH logo with practice name
     * Send email, so we can prefill and lock email input.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    public function resetUrl($notifiable)
    {
        if ( ! $this->url) {
            $this->url = route('password.reset', [
                'token'       => Password::broker('patient_users')->createToken($this->carePlan->patient),
                'practice_id' => $notifiable->getPrimaryPracticeId(),
                'email'       => $notifiable->email,
            ]);
        }

        return $this->url;
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
            'channels'         => $this->channels,
            'notifiable_email' => $notifiable->email,

            'body'    => $this->getBody(),
            'subject' => $this->getSubject(),

            'reset_url' => $this->resetUrl($notifiable),

            'careplan_id' => $this->carePlan->id,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @throws \Exception
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->from('noreply@circlelinkhealth.com', $notifiable->getPrimaryPracticeName())
            ->subject($this->getSubject())
            ->markdown('emails.patientCarePlanApproved', [
                'action_url'    => $this->getActionUrl($notifiable),
                'action_text'   => $this->getActionText(),
                'practice_name' => $notifiable->getPrimaryPracticeName(),
                'reset_url'     => $this->resetUrl($notifiable),
                'body'          => $this->getBody(),
                'is_followup'   => CarePlan::PROVIDER_APPROVED === $this->carePlan->status,
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
        return $this->channels;
    }
}
