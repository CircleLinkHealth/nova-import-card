<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Core\Contracts\FaxableNotification;
use CircleLinkHealth\Core\DTO\SimpleNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SendCareDocument extends Notification implements FaxableNotification
{
    use Queueable;

    private $channels = [
        'database',
    ];

    private $filePath;
    private $media;
    private $patient;
    private $reportType;
    private $reportYear;

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
        $this->reportYear = $this->media->getCustomProperty('year', Carbon::parse($this->media->created_at)->year);

        $this->patient = $patient;

        $this->channels = array_merge($this->channels, $channels);
    }

    public function __destruct()
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
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
        return [
            'channels'  => $this->via($notifiable),
            'sender_id' => auth()->user()
                ? auth()->user()->id
                : 'redis',
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
     * @return SimpleNotification
     */
    public function toDirectMail($notifiable)
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            throw new \Exception('Notifiable or Emr direct address not found.', 400);
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
     * @throws \Exception
     */
    public function toFax($notifiable = null): array
    {
        if ( ! $notifiable || ! $notifiable->fax) {
            throw new \Exception('Notifiable or fax number not found.', 400);
        }

        return [
            'file' => $this->toPdf(),
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
        $link = $this->getReportLink();

        return (new MailMessage())
            ->subject($this->getSubject())
            ->line("Click at link below to see the web version of the patient's AWV {$this->reportType}.")
            ->action('Go to report', $link);
    }

    /**
     * Get a pdf representation of the note.
     *
     * @return string
     */
    public function toPdf()
    {
        $this->filePath = storage_path($this->media->file_name);

        $saved = file_put_contents($this->filePath, $this->media->getFile());

        if ( ! $saved) {
            return false;
        }

        return $this->filePath;
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
            $message .= PHP_EOL.PHP_EOL.'This Report was forwarded to you by '.auth()->user()->getFullName().'.';
        }

        $message .= $lastLine;

        return $message;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    private function getReportLink()
    {
        $awvUrl = config('services.awv.report_url');

        $awvUrl = Str::replaceFirst(
            '$PATIENT_ID$',
            $this->patient->id,
            $awvUrl
        );
        $awvUrl = Str::replaceFirst(
            '$REPORT_TYPE$',
            $this->getSanitizedReportType(),
            $awvUrl
        );

        return Str::replaceFirst(
            '$YEAR$',
            $this->reportYear,
            $awvUrl
        );
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

        throw new \Exception('Invalid Report Type', 400);
    }
}
