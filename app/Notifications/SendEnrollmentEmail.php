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
    private $color;
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
     * @param null $color
     */
    public function __construct($isReminder = false, $color = null)
    {
        $this->isReminder = $isReminder;
        $this->color      = $color;
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
     * @throws \Exception
     *
     * @return array
     */
    public function toArrayData($notifiable)
    {
        if ($notifiable->checkForSurveyOnlyRole()) {
            $enrollee = Enrollee::whereUserId($notifiable->id)->first();
            if ( ! $enrollee) {
                throw new \Exception("Could not find enrollee for user[$notifiable->id]");
            }

            return $this->enrolleeArrayData($enrollee->id);
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

        $fromName = config('mail.from.name'); //@todo: We dont need to show CircleLinkHealth as default
        if ( ! empty($notifiable->primaryPractice) && ! empty($notifiable->primaryPractice->display_name)) {
            $fromName = $notifiable->primaryPractice->display_name;
        }
      
        return (new AutoEnrollmentMailChannel($fromName))
            ->from(config('mail.from.address'), $fromName)
            ->subject('Wellness Program')
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
     *
     * @return array
     */
    private function enrolleeArrayData($enrolleeId)
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
