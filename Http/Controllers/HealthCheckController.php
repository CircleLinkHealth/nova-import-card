<?php

namespace CircleLinkHealth\Core\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HealthCheckController extends Controller
{
    /**
     * Returns OK if the site is up and running.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function isSiteUp()
    {
        return \response('OK', 200);
    }
}
