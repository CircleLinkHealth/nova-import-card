<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Notifications\SendEnrollementSms;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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

        foreach ($event->userIds as $userId) {
            $user = User::findOrFail($userId);
            if ( ! $user) {
                Log::critical("Cannot find user[$userId]. Will not send enrollment sms.");
                continue;
            }

            $user->notify(new SendEnrollementSms((bool) $event->isReminder));
        }
    }
}
