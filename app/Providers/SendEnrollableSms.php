<?php

namespace App\Providers;

use App\Events\UserFromEnrolleeCreated;
use App\Notifications\SendEnrollementSms;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEnrollableSms implements ShouldQueue
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
        $this->sendSms($event->user);
    }

    private function sendSms($notifiable)
    {
        $notifiable->notify(new SendEnrollementSms());
    }
}
