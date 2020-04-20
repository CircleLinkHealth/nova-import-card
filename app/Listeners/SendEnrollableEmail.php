<?php

namespace App\Listeners;

use App\Events\UserFromEnrolleeCreated;
use App\Notifications\SendEnrollmentEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     * @param  UserFromEnrolleeCreated  $event
     * @return void
     */
    public function handle(UserFromEnrolleeCreated $event)
    {
     return $this->sendEmail($event->user);
    }

    private function sendEmail($notifiable)
    {
        $notifiable->notify(new SendEnrollmentEmail());
    }
}
