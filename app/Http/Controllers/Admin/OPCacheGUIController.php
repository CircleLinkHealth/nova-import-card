<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class OPCacheGUIController extends Controller
{
    /**
     * Show the status of OPCache.
     *
     * @return View the view from amnuts/opcache-gui
     */
    public function index()
    {
        return view()->file(storage_path('../vendor/amnuts/opcache-gui/index.php'));
    }
}
