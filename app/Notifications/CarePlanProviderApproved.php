<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Core\Contracts\DirectMailableNotification;
use CircleLinkHealth\Core\Contracts\FaxableNotification;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\Core\DTO\SimpleNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CarePlanProviderApproved extends Notification implements FaxableNotification, DirectMailableNotification
{
    use Queueable;
    public $attachment;

    public $carePlan;
    public $channels = ['database'];
    public $pathToPdf;
    /**
     * @var array
     */
    private $faxOptions = [];

    /**
     * Create a new notification instance.
     *
     * @param Note  $carePlan
     * @param array $channels
     */
    public function __construct(
        CarePlan $carePlan,
        $channels = []
    ) {
        $this->attachment = $this->carePlan = $carePlan;

        $this->channels = array_unique(array_merge($this->channels, $channels));
    }

    public function directMailBody($notifiable): string
    {
        return $this->getBody();
    }

    public function directMailSubject($notifiable): string
    {
        return $this->getSubject();
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getBody()
    {
        $message = 'Please click below button to see a Care Plan regarding one of your patients, which was approved on '
                   .$this->carePlan->provider_date->toFormattedDateString();

        $approver = optional($this->carePlan->providerApproverUser);

        if ($approver) {
            $message .= ' by '.$approver->getFullName();
        }

        return $message;
    }

    /**
     * Add any specific options for eFax API here.
     */
    public function getFaxOptions(): array
    {
        return $this->faxOptions;
    }

    /**
     * Get the mail's subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return 'A CarePlan has just been approved';
    }

    public function setFaxOptions(array $faxOptions): CarePlanProviderApproved
    {
        $this->faxOptions = $faxOptions;

        return $this;
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

            'sender_id'   => auth()->id(),
            'sender_type' => auth()->check()
                ? User::class
                : null,
            'sender_email' => optional(auth()->user())->email,

            'receiver_type'  => get_class($notifiable),
            'receiver_id'    => $notifiable->id,
            'receiver_email' => $notifiable->email,

            'body'    => $this->getBody(),
            'link'    => $this->carePlan->link(),
            'subject' => $this->getSubject(),

            'careplan_id' => $this->carePlan->id,

            'pathToPdf' => $this->pathToPdf,
        ];
    }

    /**
     * Get a pdf representation of the note to send via DM.
     *
     * @param $notifiable
     *
     * @return bool|string
     */
    public function toDirectMail($notifiable): SimpleNotification
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return (new SimpleNotification())
            ->setBody($this->directMailBody($notifiable))
            ->setSubject($this->directMailSubject($notifiable))
            ->setFilePath($this->toPdf());
    }

    /**
     * Get a pdf representation of the note to send via Fax.
     *
     * @param $notifiable
     */
    public function toFax($notifiable = null): array
    {
        return [
            'file' => $this->toPdf($notifiable),
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
        $saasAccountName     = $notifiable->saasAccountName();
        $slugSaasAccountName = strtolower(Str::slug($saasAccountName, ''));

        $mail = (new MailMessage())
            ->view('vendor.notifications.email', [
                'greeting'        => $this->getBody(),
                'actionText'      => 'View CarePlan',
                'actionUrl'       => $this->carePlan->link(),
                'introLines'      => [],
                'outroLines'      => [],
                'level'           => '',
                'saasAccountName' => $saasAccountName,
            ])
            ->from("no-reply@${slugSaasAccountName}.com", $saasAccountName)
            ->subject($this->getSubject());

        if ($notifiable->saasAccount->isCircleLinkHealth()) {
            return $mail->bcc([
                'raph@circlelinkhealth.com',
                'abigail@circlelinkhealth.com',
            ]);
        }

        return $mail;
    }

    /**
     * Get a pdf representation of the note.
     *
     * @return string
     */
    public function toPdf()
    {
        if ( ! file_exists($this->pathToPdf)) {
            $this->pathToPdf = $this->carePlan->toPdf();
        }

        return $this->pathToPdf;
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
