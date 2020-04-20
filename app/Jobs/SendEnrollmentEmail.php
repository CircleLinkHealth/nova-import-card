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
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEnrollmentEmail extends Notification implements ShouldQueue
{
    use EnrollableManagement;
    use EnrollableNotificationContent;
    use Queueable;

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
     * @var array
     */
    public $notificationContent;

    /**
     * Create a new notification instance.
     *
     * @param bool $isReminder
     */
    public function __construct($isReminder = false)
    {
        $this->isReminder = $isReminder;
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
     * @return array
     */
    public function toArrayData($notifiable)
    {
        if ($notifiable->checkForSurveyOnlyRole()) {
            $enrollee = Enrollee::whereUserId($notifiable->id)->first();

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
//        @todo: refactor this to split methods.
        $this->getNotificationContent($notifiable);

        return (new MailMessage())
            ->line($this->notificationContent['line1'])
            ->line($this->notificationContent['line2'])
            ->action('Get More Info', url($this->createInvitationLink($notifiable)));
    }

    public function getNotificationContent(User $notifiable)
    {
        $this->notificationContent = $this->emailAndSmsContent($notifiable, $this->isReminder);
        return $this->notificationContent;
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
