<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Notifications\SendEnrollementSms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;

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
    }

    /**
     * Handle the event.
     *
     * @param $event
     *
     * @return void
     */
    public function handle($event)
    {
        $this->sendSms($event);
    }

    private function sendSms($event)
    {
        if (App::environment(['testing'])) {
            return;
        }

        $event->user->notify(new SendEnrollementSms((bool) $event->isReminder));
    }
}
