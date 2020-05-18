<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Events\AutoEnrollableCollected;
use CircleLinkHealth\Customer\Entities\User;
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
        $this->sendEmail($event->userIds, $event->isReminder, $event->color);
    }

    private function sendEmail(array $userIds, bool $isReminder, string $color)
    {
        if (App::environment(['testing'])) {
            return;
        }

        User::whereIn('id', $userIds)->get()->each(function (User $user) use ($isReminder, $color) {
            \App\Jobs\SendEnrollmentEmail::dispatch($user, $isReminder, $color);
        });
    }
}
