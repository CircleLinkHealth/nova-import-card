<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyPatientCarePlanApproved extends Notification
{
    use Queueable;

    private $carePlan;

    private $channels = ['database'];

    /**
     * This notification is sent to the patient both when Careplan is QA approved by CLH, and when it's Provider approved.
     * The first case we send the patient a button link to the password reset page, while on the second we send them a link to
     * CPM, with a hyperlink of the reset page below.
     */

    /**
     * Create a new notification instance.
     *
     * @param CarePlan $carePlan
     * @param array    $channels
     */
    public function __construct(CarePlan $carePlan, array $channels = ['mail'])
    {
        $this->channels = array_merge($channels, $this->channels);
        $this->carePlan = $carePlan;
    }

    public function getActionText()
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status ? 'Setup Password' : 'Go to Care Plan Manager';
    }

    public function getActionUrl()
    {
        return CarePlan::PROVIDER_APPROVED != $this->carePlan->status ? url('auth/password/reset') : url('/');
    }

    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getBody()
    {
        if (CarePlan::PROVIDER_APPROVED === $this->carePlan->status) {
            $message = 'Please click below button to see your Care Plan, which was approved on '
                       .$this->carePlan->provider_date->toFormattedDateString();

            $approver = optional($this->carePlan->providerApproverUser);

            if ($approver) {
                $message .= ' by '.$approver->getFullName().'.';
            }

            return $message;
        }

        return 'Your Care Plan is pending Dr. approval. 
        Please click on the button below to setup a password for Care Plan Manager. 
        This will send a password reset email to your address.';
    }

    /**
     * Get the mail's subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return 'Your CarePlan has just been approved';
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
        return (new MailMessage())
            ->from('noreply@circlelinkhealth.com')
            ->subject($this->getSubject())
            ->markdown('emails.patientCarePlanApproved', [
                'action_url'  => $this->getActionUrl(),
                'action_text' => $this->getActionText(),
                'reset_url'   => url('auth/password/reset'),
                'body'        => $this->getBody(),
                'is_followup' => CarePlan::PROVIDER_APPROVED === $this->carePlan->status,
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
