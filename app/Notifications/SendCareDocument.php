<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\ValueObjects\SimpleNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCareDocument extends Notification
{
    use Queueable;

    private $channels = [
        'database',
    ];
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
    public function __construct(Media $media, User $patient, $channels = [])
    {
        $this->media = $media;

        $this->reportType = $this->media->getCustomProperty('doc_type');

        $this->patient = $patient;

        $this->channels = array_merge($this->channels, $channels);
    }

    /**
     * Get the body of a DM.
     *
     * @return string
     */
    public function getDMBody()
    {
        $link = $this->getReportLink();

        $message  = "Please find attached an AWV {$this->reportType} regarding one of your patients";
        $lastLine = PHP_EOL.PHP_EOL."The web version of the report can be found at $link";

        return $this->getBody($message, $lastLine);
    }

    /**
     * Get the mail's subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return "You have been forwarded an AWV {$this->reportType} from CarePlanManager";
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
        //todo: make sure we save path of file if it exists so we can delete the file from storage (if direct or fax)
        return [
            'channels'   => $this->via($notifiable),
            'sender_id'  => auth()->user()->id,
            'patient_id' => $this->patient->id,
            'media_id'   => $this->media->id,
        ];
    }

    /**
     * Get a pdf representation of the note to send via DM.
     *
     * @param $notifiable
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public function toDirectMail($notifiable)
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            throw new \Exception('File retrieved is not in json format.', 500);

            return false;
        }

        return (new SimpleNotification())
            ->setBody($this->getDMBody())
            ->setSubject($this->getSubject())
            ->setFilePath($this->toPdf());
    }

    /**
     * Get a pdf representation of the report to send via Fax.
     *
     * @param $notifiable
     *
     * @return bool|string
     */
    public function toFax($notifiable)
    {
        if ( ! $notifiable || ! $notifiable->fax) {
            return false;
        }

        return $this->toPdf();
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
        //todo: add more details to message?
        $link = $this->getReportLink();

        return (new MailMessage())
            ->subject($this->getSubject())
            ->line("Click at link below to see patient {$this->reportType}")
            ->action('Go to report', $link);
    }

    /**
     * Get a pdf representation of the note.
     *
     * @return string
     */
    public function toPdf()
    {
        $currentDateTime = Carbon::now();
        $path            = storage_path("{$this->reportType}_patient_id_{$this->patient->id}_{$currentDateTime->toDateTimeString()}.pdf");

        $saved = file_put_contents($path, $this->media->getFile());

        if ( ! $saved) {
            return false;
        }

        //todo:make sure we delete the file in successful notification event - maybe the path will be needed on the notification itself so the even knows -

        return $path;
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
        if (is_a($notifiable, AnonymousNotifiable::class)) {
            return array_merge($this->channels, [array_key_first($notifiable->routes)]);
        }

        return $this->channels;
    }

    /**
     * Factory for message body.
     *
     * @param $greeting
     * @param mixed $lastLine
     *
     * @return string
     */
    private function getBody($greeting, $lastLine = '')
    {
        $message = $greeting.', created on '
                   .$this->media->created_at->toFormattedDateString();

        if (auth()->check()) {
            //todo:double check if auth user is the right way to go
            $message .= PHP_EOL.PHP_EOL.'This Report was forwarded to you by '.auth()->user()->getFullName().'.';
        }

        $message .= $lastLine;

        return $message;
    }

    private function getReportLink()
    {
        $awvUrl = config('services.awv.url');

        $reportTypeForUrl = $this->getSanitizedReportType();

        $year = ! is_a($this->media->created_at, 'Carbon\Carbon')
            ? Carbon::parse($this->media->created_at)->year
            : $this->media->year;

        return $awvUrl."/get-patient-report/{$this->patient->id}/{$reportTypeForUrl}/{$year}";
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
