<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyPatientCarePlanApproved extends Notification
{
    use Queueable;

    private $carePlan;

    private $channels = ['database'];

    /**
     * This notification is sent to the patient both when Careplan is QA approved by CLH, and when it's Provider
     * approved. The first case we send the patient a button link to the password reset page, while on the second we
     * send them a link to CPM, with a hyperlink of the reset page below.
     */

    /**
     * Create a new notification instance.
     */
    public function __construct(CarePlan $carePlan, array $channels = ['mail'])
    {
        $this->channels = array_merge($channels, $this->channels);
        $this->carePlan = $carePlan;
    }

    public function getActionText()
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status
            ? 'Setup Password'
            : 'Login to View Care Plan';
    }

    public function getActionUrl()
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status
            ? url('auth/password/reset')
            : url('/');
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
            return 'Your CarePlan has just been approved';
        }

        return 'Your Care Plan has been sent to your doctor for approval';
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
            'channels' => $this->channels,

            'body'    => $this->getBody(),
            'subject' => $this->getSubject(),

            'careplan_id' => $this->carePlan->id,
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
        $practice = $this->carePlan->patient->primaryPractice;

        return (new MailMessage())
            ->from('noreply@circlelinkhealth.com')
            ->subject($this->getSubject())
            ->markdown('emails.patientCarePlanApproved', [
                'action_url'    => $this->getActionUrl(),
                'action_text'   => $this->getActionText(),
                'practice_name' => $practice->display_name,
                'reset_url'     => url('auth/password/reset'),
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
