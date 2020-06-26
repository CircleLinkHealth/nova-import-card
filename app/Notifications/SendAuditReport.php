<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\DirectMailableNotification;
use App\Contracts\FaxableNotification;
use App\Contracts\NotificationAboutPatient;
use App\Reports\PatientDailyAuditReport;
use App\ValueObjects\SimpleNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SendAuditReport extends Notification implements FaxableNotification, DirectMailableNotification, ShouldQueue, NotificationAboutPatient
{
    use Queueable;

    /**
     * @var array
     */
    public $channels;
    /**
     * @var Carbon
     */
    public $date;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $pathToPdf;
    /**
     * @var User
     */
    public $patient;
    /**
     * @var bool
     */
    private $batchSend;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $patient, Carbon $date, array $channels, bool $batchSend = true)
    {
        $this->patient   = $patient;
        $this->date      = $date;
        $this->channels  = $channels;
        $this->batchSend = $batchSend;
    }

    public function directMailBody($notifiable): string
    {
        return "Please find attached a PDF file of the Audit Report for {$this->date->format('M Y')} for Patient with ID {$this->patient->id}";
    }

    public function directMailSubject($notifiable): string
    {
        return "Patient ID: {$this->patient->id} {$this->date->format('M Y')} Audit Report";
    }

    public function getFaxOptions(): array
    {
        if (true === $this->batchSend) {
            return [
                'batch_delay'               => 60,
                'batch_collision_avoidance' => true,
            ];
        }

        return [];
    }

    public function getPdfFilename()
    {
        return $this->fileName;
    }

    /**
     * Get a pdf representation of the note.
     *
     * @return string
     */
    public function getPdfPath()
    {
        if ( ! file_exists($this->pathToPdf)) {
            $response = (new PatientDailyAuditReport(
                $this->patient,
                $this->date->startOfMonth()
            ))
                ->renderPDF();

            $this->fileName = $response['file_name'];

            $this->pathToPdf = $response['path'];
        }

        return $this->pathToPdf;
    }

    public function notificationAboutPatientWithUserId(): int
    {
        return $this->patient->id;
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
            'channels'              => $this->channels,
            'directMailBody'        => $this->directMailBody($notifiable),
            'directMailSubject'     => $this->directMailSubject($notifiable),
            'getPdfPath'            => $this->getPdfPath(),
            'getPdfFilename'        => $this->getPdfFilename(),
            'media_collection_name' => PatientDailyAuditReport::mediaCollectionName($this->date),
        ];
    }

    public function toDirectMail($notifiable): SimpleNotification
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return (new SimpleNotification())
            ->setSubject($this->directMailSubject($notifiable))
            ->setBody($this->directMailBody($notifiable))
            ->setFilePath($this->getPdfPath())
            ->setFileName($this->getPdfFilename());
    }

    public function toFax($notifiable = null): array
    {
        return [
            'file' => $this->getPdfPath(),
        ];
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
