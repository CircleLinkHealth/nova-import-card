<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCareDocument extends Notification
{
    use Queueable;

    private $channels = ['database'];
    private $media;
    private $patient;
    private $reportType;

    /**
     * Create a new notification instance.
     *
     * @param mixed $media
     * @param mixed $patient
     * @param mixed $channels
     */
    public function __construct(Media $media, User $patient, $channels = ['mail'])
    {
        $this->media = $media;

        $this->reportType = $this->media->getCustomProperty('doc_type');

        $this->patient = $patient;

        $this->channels = array_merge($this->channels, $channels);
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
            'channels'   => $this->channels,
            'sender_id'  => auth()->user()->id,
            'patient_id' => $this->patient->id,
            'media_id'   => $this->media->id,
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
        $awvUrl = config('services.awv.url');

        $reportTypeForUrl = $this->getSanitizedReportType();

        $year = ! is_a($this->media->created_at, 'Carbon\Carbon')
            ? Carbon::parse($this->media->created_at)->year
            : $this->media->year;

        $url = $awvUrl."/get-patient-report/{$this->patient->id}/{$reportTypeForUrl}/{$year}";

        //todo: add more details to message?
        return (new MailMessage())
            ->subject("Patient {$this->reportType} - {$this->patient->getPrimaryPracticeName()}")
            ->line("Click at link below to see patient {$this->reportType}")
            ->action('Go to report', $url);
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

    private function getSanitizedReportType()
    {
        $type = $this->reportType;

        if ('PPP' == $type) {
            return 'ppp';
        }

        if ('Provider Report' == $type) {
            return 'provider-report';
        }

        throw new \Exception('Invalid Report Type', 500);
    }
}
