<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Notifications\SendEnrollmentEmail;
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
    public function handle($event)
    {
        $this->sendEmail($event);
    }

    /**
     * @param $event
     */
    private function sendEmail($event)
    {
        foreach ($event->userIds as $userId) {
            $user = User::findOrFail($userId); // Just in case.
        }
        $user->notify(new SendEnrollmentEmail($event->isReminder, $event->color));
    }
}
