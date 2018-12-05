<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The current proxy header mappings.
     *
     * @var array
     */
    protected $headers = [
        Request::HEADER_FORWARDED         => 'FORWARDED',
        Request::HEADER_X_FORWARDED_FOR   => 'X_FORWARDED_FOR',
        Request::HEADER_X_FORWARDED_HOST  => 'X_FORWARDED_HOST',
        Request::HEADER_X_FORWARDED_PORT  => 'X_FORWARDED_PORT',
        Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
    ];
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;
}
