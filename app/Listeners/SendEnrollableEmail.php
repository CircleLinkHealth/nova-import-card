<?php

namespace App\Listeners;

use App\Events\UserFromEnrolleeCreated;
use App\Notifications\SendEnrollmentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEnrollableEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $this->sendEmail($event->user);
    }

    private function sendEmail($notifiable)
    {
        $notifiable->notify(new SendEnrollmentEmail());
    }
}
