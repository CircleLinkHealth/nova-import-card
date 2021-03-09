<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime;

use Laravel\Vapor\Runtime\Handlers\CliHandler;
use Laravel\Vapor\Runtime\Handlers\QueueHandler;

class CliHandlerFactory
{
    /**
     * Create a new handler for the given CLI event.
     *
     * @return mixed
     */
    public static function make(array $event)
    {
        return isset($event['Records'][0]['messageId'])
                    ? new QueueHandler()
                    : new CliHandler();
    }
}
