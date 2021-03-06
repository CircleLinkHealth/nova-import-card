<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime;

use Laravel\Vapor\Runtime\Handlers\FpmHandler;
use Laravel\Vapor\Runtime\Handlers\LoadBalancedFpmHandler;
use Laravel\Vapor\Runtime\Handlers\UnknownEventHandler;
use Laravel\Vapor\Runtime\Handlers\WarmerHandler;
use Laravel\Vapor\Runtime\Handlers\WarmerPingHandler;

class HttpHandlerFactory
{
    /**
     * Create a new handler for the given HTTP event.
     *
     * @return \Laravel\Vapor\Contracts\LambdaEventHandler
     */
    public static function make(array $event)
    {
        if (isset($event['vaporWarmer'])) {
            return new WarmerHandler();
        }
        if (isset($event['vaporWarmerPing'])) {
            return new WarmerPingHandler();
        }
        if (isset($event['requestContext']['elb'])) {
            return new LoadBalancedFpmHandler();
//            return new LoadBalancedAppHandler;
        }
        if (isset($event['httpMethod'])) {
            return new FpmHandler();
            // return new AppHandler;
        }

        return new UnknownEventHandler();
    }
}
