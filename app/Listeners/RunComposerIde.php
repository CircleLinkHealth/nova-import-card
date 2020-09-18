<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Core\Traits\RunsConsoleCommands;

class RunComposerIde
{
    use RunsConsoleCommands;

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        if (app()->environment('local')) {
            $this->runCpmCommand(['composer', 'ide'], true, CpmConstants::TEN_MINUTES_IN_SECONDS);
        }
    }
}
