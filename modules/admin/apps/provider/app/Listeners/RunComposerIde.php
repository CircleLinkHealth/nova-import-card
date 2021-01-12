<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Constants;
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
            $this->runCpmCommand(['composer', 'ide'], true, Constants::TEN_MINUTES_IN_SECONDS);
        }
    }
}
