<?php

namespace App\Notifications;
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */


use App\Traits\EnrollableManagement;
use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEnrollmentEmail extends Notification implements ShouldQueue
{
    use EnrollableManagement;
    use EnrollableNotificationContent;
    use Queueable;
    const ENROLLEE = Enrollee::class;
    const USER = User::class;

    /**
     * @var null
     */
    private $enrolleeModelId;
    /**
     * @var bool
     */
    private $isReminder;

    /**
     * @var mixed
     */
    private $receiver;

    /**
     * Create a new notification instance.
     *
     * @param User $enrollable
     * @param bool $isReminder
     */
    public function __construct(User $enrollable, $isReminder = false)
    {
        $this->receiver = $enrollable;
        $this->isReminder = $isReminder;
    }

    // Returns Notifiable model
    public function attachmentType(): string
    {
        return $this->receiver;
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->receiver;
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
        return $this->toArrayData($notifiable);
    }

    /**
     * @return array
     */
    public function toArrayData(User $notifiable)
    {
        if ($notifiable->checkForSurveyOnlyRole()) {
            $enrollee = Enrollee::whereUserId($this->receiver->id)->first();

            return [
                'enrollee_id' => $enrollee->id,
                'is_reminder' => $this->isReminder,
                'is_survey_only' => true,
            ];
        }

        return [];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);

        return (new MailMessage())
            ->line($notificationContent['line1'])
            ->line($notificationContent['line2'])
            ->action('Get More Info', url($this->createInvitationLink($notifiable, $notificationContent['urlData'])));
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
        return ['database', 'mail'];
    }
}
