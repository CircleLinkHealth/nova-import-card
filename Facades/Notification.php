<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Facades;

use CircleLinkHealth\Core\Entities\AnonymousNotifiable;

class Notification extends \Illuminate\Support\Facades\Notification
{
    /**
     * Begin sending a notification to an anonymous notifiable.
     *
     * EDITED:
     * Override the built-in method so we can return our custom AnonymousNotifiable class
     *
     * @param string $channel
     * @param mixed  $route
     *
     * @return \Illuminate\Notifications\AnonymousNotifiable
     */
    public static function route($channel, $route)
    {
        return (new AnonymousNotifiable())->route($channel, $route);
    }
}
