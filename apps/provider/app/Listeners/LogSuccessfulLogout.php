<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\LogSuccessfulLogoutToDB;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogout implements ShouldQueue, ShouldBeEncrypted
{
    use InteractsWithQueue;

    public function handle(Logout $event)
    {
        LogSuccessfulLogoutToDB::dispatch($event)->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }
}
