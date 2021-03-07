<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class HealthCheckController extends Controller
{
    /**
     * Returns OK if the site is up and running.
     */
    public function isSiteUp()
    {
        return response()->json('OK', 200);
    }
}
