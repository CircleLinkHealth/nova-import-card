<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Http\Controllers;

use Illuminate\Routing\Controller;

class OPCacheGUIController extends Controller
{
    /**
     * Show the status of OPCache.
     */
    public function index()
    {
        return view()->file(base_path('vendor/amnuts/opcache-gui/index.php'));
    }
}
