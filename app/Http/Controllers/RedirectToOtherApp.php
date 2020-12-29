<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectToOtherApp extends Controller
{
    public function config()
    {
        dd(config());
    }

    public function pinfo()
    {
        dd(phpinfo());
    }

    public function redirectToProvider(Request $request)
    {
        $url = $request->getRequestUri();

        return redirect()->to(rtrim(config('core.apps.cpm-provider.url'), '/')."$url");
    }
}
