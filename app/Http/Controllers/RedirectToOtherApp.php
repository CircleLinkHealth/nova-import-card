<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers;

class RedirectToOtherApp extends Controller
{
    public function ccdImporter()
    {
        return redirect()->to('');
    }
    
    public function pinfo() {
        dd(phpinfo());
    }
}
