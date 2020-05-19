<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\AutoEnrollableCollected;
use CircleLinkHealth\Customer\Entities\User;
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
    public function handle(AutoEnrollableCollected $event)
    {
        $this->sendSms($event->userIds, (bool) $event->isReminder);
    }

    private function sendSms(array $userIds, bool $isReminder)
    {
        if (App::environment(['testing'])) {
            return;
        }

        User::whereIn('id', $userIds)->get()->each(function (User $user) use ($isReminder) {
            \App\Jobs\SendEnrollmentSms::dispatch($user, $isReminder);
        });
    }
}
