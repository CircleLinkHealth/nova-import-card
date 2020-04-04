<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class AppointmentsReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $to;
    private $url;

    /**
     * AppointmentsReminderNotification constructor.
     *
     * @param $to
     * @param $url
     */
    public function __construct($to, $url)
    {
        $this->to  = $to;
        $this->url = $url;
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
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $message = "Upcoming AWV appointments! Click here [$this->url] to see the list.";

        return (new SlackMessage())
            ->content($message)
            ->from('Sunnie Bots', ':sunflower:')
            ->to($this->to);
    }
}
