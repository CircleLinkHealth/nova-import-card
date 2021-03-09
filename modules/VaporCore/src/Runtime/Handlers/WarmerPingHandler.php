<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime\Handlers;

use Laravel\Vapor\Contracts\LambdaEventHandler;
use Laravel\Vapor\Runtime\ArrayLambdaResponse;

class WarmerPingHandler implements LambdaEventHandler
{
    /**
     * Handle an incoming Lambda event.
     *
     * @param  \Laravel\Vapor\Contracts\LambdaResponse
     */
    public function handle(array $event)
    {
        usleep(50 * 1000);

        return new ArrayLambdaResponse([
            'output' => 'Warmer ping handled.',
        ]);
    }
}
