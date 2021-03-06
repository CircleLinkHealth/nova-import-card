<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Contracts;

interface LambdaEventHandler
{
    /**
     * Handle an incoming Lambda event.
     *
     * @param  \Laravel\Vapor\Contracts\LambdaResponse
     */
    public function handle(array $event);
}
