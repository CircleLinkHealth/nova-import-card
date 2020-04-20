<?php

namespace App\Listeners;

use App\Notifications\SendEnrollmentEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        $this->sendEmail($event);
    }

    /**
     * @param $event
     */
    private function sendEmail($event)
    {
        $event->user->notify(new SendEnrollmentEmail($event->isReminder));
    }
}
