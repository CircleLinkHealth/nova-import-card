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
        if (str_contains(app()->environment(), 'prod')) {
            return 'not available on this environment';
        }
        dd(config());
    }

    public function pinfo()
    {
        if (str_contains(app()->environment(), 'prod')) {
            return 'not available on this environment';
        }
        dd(phpinfo());
    }

    public function redirectToProvider(Request $request)
    {
        $url = $request->getRequestUri();

        return redirect()->to(rtrim(config('core.apps.cpm-provider.url'), '/')."$url");
    }
}
