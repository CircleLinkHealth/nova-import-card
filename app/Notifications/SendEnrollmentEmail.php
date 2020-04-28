<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Notifications\Channels\AutoEnrollmentMailChannel;
use App\Traits\EnrollableManagement;
use App\Traits\EnrollableNotificationContent;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEnrollmentEmail extends Notification implements ShouldQueue
{
    use EnrollableManagement;
    use EnrollableNotificationContent;
    use Queueable;

    const USER = User::class;
    /**
     * @var array
     */
    public $notificationContent;
    /**
     * @var null
     */
    private $enrolleeModelId;
    /**
     * @var bool
     */
    private $isReminder;

    /**
     * Create a new notification instance.
     *
     * @param bool $isReminder
     */
    public function __construct($isReminder = false)
    {
        $this->isReminder = $isReminder;
    }

    public function getNotificationContent(User $notifiable)
    {
        $this->notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);

        return $this->notificationContent;
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
     * @param $notifiable
     *
     * @return array
     */
    public function toArrayData($notifiable)
    {
        if ($notifiable->checkForSurveyOnlyRole()) {
            $enrollee = Enrollee::whereUserId($notifiable->id)->first();

            return $this->enrolleeArraData($enrollee->id);
        }

        return $this->patientArrayData();
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
        $this->getNotificationContent($notifiable);

        return (new AutoEnrollmentMailChannel())
            ->line($this->notificationContent['line1'])
            ->line($this->notificationContent['line2'])
            ->action('Get my Care Coach', url($this->createInvitationLink($notifiable)));
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

    /**
     * @param $enrolleeId
     * @return array
     */
    private function enrolleeArraData($enrolleeId)
    {
        return [
            'enrollee_id'    => $enrolleeId,
            'is_reminder'    => $this->isReminder,
            'is_survey_only' => true,
        ];
    }

    /**
     * @return array
     */
    private function patientArrayData()
    {
        return [
            'is_reminder'    => $this->isReminder,
            'is_survey_only' => false,
        ];
    }
}
