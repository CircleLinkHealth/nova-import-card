<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendReport extends Notification
{
    use Queueable;

    public $patient;

    public $report;

    public $type;

    public $channels = ['database'];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($patient, $report, $type, $channels = ['mail'])
    {
        $this->patient = $patient;

        $this->type = $type;

        $this->report  = $report;

        $this->channels = array_merge($this->channels, $channels);

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    public function toDirectMail($notifiable)
    {
        //to implement. Needs work to move Direct Mail related channels to customer
    }


    public function toFax($notifiable)
    {
        //to implement. Needs work to move Direct Mail related channels to customer

//        if ( ! $notifiable || ! $notifiable->fax) {
//            return false;
//        }
//
//        return true;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $saasAccountName     = $notifiable->saasAccountName();
        $slugSaasAccountName = strtolower(str_slug($saasAccountName, ''));

        $mail = (new MailMessage())
            ->view(
                'notifications.email',
                [
                    'greeting'        => "Please click below button to see the AWV {$this->type} for patient with id: {$this->patient->id}",
                    'actionText'      => 'View Report',
                    'actionUrl'       => $this->getActionUrl(),
                    'introLines'      => [],
                    'outroLines'      => [],
                    'level'           => '',
                    'saasAccountName' => $saasAccountName,
                ]
            )
            ->from("no-reply@${slugSaasAccountName}.com", $saasAccountName)
            ->subject("Patient AWV report: {$this->type}");


        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'report' => $this->report->toArray()
        ];
    }

    private function getActionUrl(){

        if ($this->type == 'PPP'){
            return route('get-ppp-report', ['userId' => $this->patient->id]);
        }else{
            return route('get-provider-report', ['userId' => $this->patient->id]);
        }


    }
}
